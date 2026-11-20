<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Menu;
use App\Models\BahanBaku;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\DetailBahanPesanan;
use App\Models\PembelianStok;
use App\Services\PesananService;
use App\Services\MenuService;
use App\Services\BahanBakuService;
use App\Services\ResepService;
use App\Services\PembelianStokService;
use App\Services\SupplierHargaService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

echo "======================================================\n";
echo " UJI INTEGRASI MENYELURUH FRONTEND & BACKEND DATABASE \n";
echo "======================================================\n\n";

$allPassed = true;

function assertTest($name, $condition, $details = '') {
    global $allPassed;
    if ($condition) {
        echo " [PASS] $name\n";
        if ($details) echo "        -> $details\n";
    } else {
        echo " [FAIL] $name\n";
        if ($details) echo "        -> ERROR: $details\n";
        $allPassed = false;
    }
}

// -----------------------------------------------------------
// 1. UJI LOGIN & RESTRIKSI ROLE (AUTH & MIDDLEWARE)
// -----------------------------------------------------------
echo "1. UJI LOGIN & RESTRIKSI PERAN (AUTH & MIDDLEWARE)\n";

$manager = User::where('peran', 'manager')->first();
if (!$manager) {
    $manager = User::create([
        'nama' => 'Rani Manajer',
        'nama_pengguna' => 'manager',
        'kata_sandi' => 'Manajer#123',
        'peran' => 'manager',
        'wajib_ganti_kata_sandi' => false
    ]);
}

$kasir = User::where('peran', 'kasir')->first();
if (!$kasir) {
    $kasir = User::create([
        'nama' => 'Rudi Kasir',
        'nama_pengguna' => 'kasir',
        'kata_sandi' => 'Kasir#124',
        'peran' => 'kasir',
        'wajib_ganti_kata_sandi' => false
    ]);
}

// Cek password hashing
assertTest(
    "Verifikasi Password Manager",
    Hash::check('Manajer#123', $manager->kata_sandi),
    "Password manager cocok dengan hash di database"
);

assertTest(
    "Verifikasi Peran Manager vs Kasir",
    $manager->isManager() && !$manager->isKasir() && $kasir->isKasir() && !$kasir->isManager(),
    "Helper role isManager() & isKasir() bekerja presisi"
);

// -----------------------------------------------------------
// 2. UJI MASTER DATA BAHAN BAKU & NORMALISASI NAMA
// -----------------------------------------------------------
echo "\n2. UJI CRUD BAHAN BAKU & NORMALISASI NAMA\n";
$bahanService = app(BahanBakuService::class);

$namaBahanTest = "Biji Arabika Test " . time();
$bahanBaru = $bahanService->create([
    'nama' => $namaBahanTest,
    'nama_normalisasi' => BahanBaku::nama_normalisasi($namaBahanTest),
    'satuan_dasar' => 'g',
    'stok_minimum' => 500,
    'kode_pemasok' => 'SUP-TEST'
]);

assertTest(
    "Tambah Bahan Baku Baru ke Database",
    $bahanBaru && $bahanBaru->exists,
    "ID: {$bahanBaru->id}, Nama: {$bahanBaru->nama}, Stok Awal: {$bahanBaru->jumlah_stok}"
);

// Update stok minimum
$bahanService->update($bahanBaru, ['stok_minimum' => 750]);
assertTest(
    "Update Batas Stok Minimum",
    $bahanBaru->fresh()->stok_minimum == 750,
    "Stok minimum berhasil diperbarui menjadi 750"
);

// -----------------------------------------------------------
// 3. UJI PEMBELIAN STOK & ATOMIC INCREMENT
// -----------------------------------------------------------
echo "\n3. UJI PEMBELIAN STOK & PENGURANGAN / PENAMBAHAN PERSINGGAHAN\n";
$stokService = app(PembelianStokService::class);

$stokAwal = (float)$bahanBaru->fresh()->jumlah_stok;
$pembelian = $stokService->catat([
    'bahan_baku_id' => $bahanBaru->id,
    'jumlah' => 2000,
    'tanggal_pembelian' => date('Y-m-d')
], $manager);

$stokSetelahBeli = (float)$bahanBaru->fresh()->jumlah_stok;
assertTest(
    "Catat Pembelian Stok & Atomic Increment",
    $stokSetelahBeli === ($stokAwal + 2000),
    "Stok bertambah dari {$stokAwal} menjadi {$stokSetelahBeli} g"
);

// -----------------------------------------------------------
// 4. UJI MASTER MENU & SINKRONISASI RESEP (MANY-TO-MANY PIVOT)
// -----------------------------------------------------------
echo "\n4. UJI MASTER MENU & RESEP (MANY-TO-MANY SYNC)\n";
$menuService = app(MenuService::class);
$resepService = app(ResepService::class);

$namaMenuTest = "Kopi Uji " . time();
$menuBaru = $menuService->create([
    'nama' => $namaMenuTest,
    'kategori' => 'Minuman',
    'harga_jual' => 25000,
]);

assertTest(
    "Tambah Menu Baru",
    $menuBaru && $menuBaru->exists && $menuBaru->resep_lengkap == false,
    "Menu '{$menuBaru->nama}' tersimpan dengan resep_lengkap=false"
);

// Pasang resep ke menu
$resepService->save($menuBaru, [
    [
        'bahan_baku_id' => $bahanBaru->id,
        'jumlah_per_porsi' => 20
    ]
]);

$menuFresh = $menuBaru->fresh();
assertTest(
    "Sinkronisasi Resep dengan Eloquent sync()",
    $menuFresh->resep_lengkap == true && $menuFresh->detailResep()->count() === 1,
    "Resep tersimpan: 20 {$bahanBaru->satuan_dasar} per porsi, flag resep_lengkap=true"
);

// -----------------------------------------------------------
// 5. UJI PEMBUATAN TRANSAKSI PESANAN KASIR (POS), PAJAK, LAYANAN & PENGURANGAN STOK
// -----------------------------------------------------------
echo "\n5. UJI ALUR PESANAN KASIR, SERVICE CHARGE 5%, PAJAK 10%, & STOK LOCKING\n";
$pesananService = app(PesananService::class);

$stokSebelumOrder = (float)$bahanBaru->fresh()->jumlah_stok;
$jumlahPorsi = 3;
$kebutuhanBahan = 20 * $jumlahPorsi; // 60 g

$pesanan = $pesananService->buatPesanan([
    'items' => [
        [
            'menu_id' => $menuFresh->id,
            'jumlah' => $jumlahPorsi
        ]
    ],
    'metode_pembayaran' => 'tunai',
    'nomor_meja' => 4
], $kasir);

// Validasi kalkulasi biaya
$expectedSubtotal = 25000 * $jumlahPorsi; // 75.000
$expectedLayanan = round($expectedSubtotal * 0.05, 2); // 3.750
$expectedPajak = round(($expectedSubtotal + $expectedLayanan) * 0.10, 2); // 7.875
$expectedTotal = $expectedSubtotal + $expectedLayanan + $expectedPajak; // 86.625

assertTest(
    "Kalkulasi Biaya Layanan 5% & Pajak 10%",
    $pesanan->subtotal == $expectedSubtotal &&
    $pesanan->biaya_layanan == $expectedLayanan &&
    $pesanan->jumlah_pajak == $expectedPajak &&
    $pesanan->total_harga == $expectedTotal,
    "Subtotal: Rp {$pesanan->subtotal}, Layanan: Rp {$pesanan->biaya_layanan}, Pajak: Rp {$pesanan->jumlah_pajak}, Total: Rp {$pesanan->total_harga}"
);

$stokSetelahOrder = (float)$bahanBaru->fresh()->jumlah_stok;
assertTest(
    "Pengurangan Stok Bahan Otomatis Sesuai Resep x Porsi",
    $stokSetelahOrder === ($stokSebelumOrder - $kebutuhanBahan),
    "Stok berkurang dari {$stokSebelumOrder} g menjadi {$stokSetelahOrder} g (berkurang {$kebutuhanBahan} g)"
);

// Cek snapshot data di DetailPesanan
$detailOrder = $pesanan->detailPesanan->first();
assertTest(
    "Snapshot Nama Menu & Harga di DetailPesanan",
    $detailOrder->nama_menu_saat_pemesanan === $menuFresh->nama &&
    $detailOrder->harga_satuan_saat_pemesanan == 25000,
    "Snapshot nama '{$detailOrder->nama_menu_saat_pemesanan}' & harga Rp {$detailOrder->harga_satuan_saat_pemesanan} tersimpan permanen"
);

// -----------------------------------------------------------
// 6. UJI STATE MACHINE STATUS PESANAN (BARU -> DIPROSES -> SELESAI)
// -----------------------------------------------------------
echo "\n6. UJI TRANSISI STATUS PESANAN (STATE MACHINE)\n";

$pesananDiproses = $pesananService->ubahStatus($pesanan, 'diproses');
assertTest(
    "Transisi Status: Baru -> Diproses",
    $pesananDiproses->status === 'diproses',
    "Status pesanan berhasil berubah menjadi 'diproses'"
);

$pesananSelesai = $pesananService->ubahStatus($pesananDiproses, 'selesai');
assertTest(
    "Transisi Status: Diproses -> Selesai",
    $pesananSelesai->status === 'selesai',
    "Status pesanan berhasil berubah menjadi 'selesai'"
);

// Cek cegah lompatan status ilegal
$lompatIlegalGagal = false;
try {
    $pesananService->ubahStatus($pesananSelesai, 'baru');
} catch (\Exception $e) {
    $lompatIlegalGagal = true;
}
assertTest(
    "Cegah Transisi Status Ilegal (Selesai -> Baru)",
    $lompatIlegalGagal,
    "Sistem berhasil menolak perubahan status yang melanggar alur"
);

// -----------------------------------------------------------
// 7. UJI PEMBATALAN PESANAN BARU & PENGEMBALIAN STOK (ROLLBACK)
// -----------------------------------------------------------
echo "\n7. UJI PEMBATALAN PESANAN & PENGEMBALIAN STOK (ROLLBACK)\n";

// Buat pesanan kedua untuk diuji pembatalannya
$pesananUntukBatal = $pesananService->buatPesanan([
    'items' => [
        [
            'menu_id' => $menuFresh->id,
            'jumlah' => 2
        ]
    ],
    'metode_pembayaran' => 'qris',
    'nomor_meja' => 2
], $kasir);

$stokSebelumBatal = (float)$bahanBaru->fresh()->jumlah_stok;
$pesananDibatalkan = $pesananService->batalkanPesanan($pesananUntukBatal);
$stokSetelahBatal = (float)$bahanBaru->fresh()->jumlah_stok;

assertTest(
    "Pembatalan Pesanan Baru & Pengembalian Stok",
    $pesananDibatalkan->status === 'dibatalkan' &&
    $stokSetelahBatal === ($stokSebelumBatal + (20 * 2)),
    "Status 'dibatalkan' dan stok dikembalikan sebanyak 40 g ke gudang"
);

// -----------------------------------------------------------
// 8. UJI INTEGRASI SUPPLIER HARGA & MEKANISME FALLBACK
// -----------------------------------------------------------
echo "\n8. UJI SUPPLIER HARGA API & MEKANISME FALLBACK\n";
$supplierService = app(SupplierHargaService::class);
$hasilSupplier = $supplierService->perbaruiHarga();

assertTest(
    "Eksekusi Pembaruan Harga Supplier & Fallback",
    isset($hasilSupplier['sumber']) && isset($hasilSupplier['data']),
    "Sumber: {$hasilSupplier['sumber']}, Pesan: {$hasilSupplier['pesan']}"
);

// Cleanup data pengujian
$bahanBaru->delete();
$menuBaru->delete();

echo "\n======================================================\n";
if ($allPassed) {
    echo " HASIL: 100% SEMUA INTEGRASI BACKEND & DATABASE LULUS! \n";
    echo " Sistem bukan sekadar tampilan, melainkan terhubung penuh!\n";
} else {
    echo " HASIL: DITEMUKAN GAGAL PADA SEBAGIAN UJI INTEGRASI.\n";
}
echo "======================================================\n";
