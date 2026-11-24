<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\DetailBahanPesanan;
use App\Models\DetailPesanan;
use App\Models\DetailResep;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PesananService
{
    /**
     * Membuat pesanan dan mengurangi stok bahan.
     *
     * [KEYWORD UKK: SERVICE LAYER]
     * Logika bisnis dipusatkan di sini agar Controller tetap ramping (Skinny Controller).
     */
    public function buatPesanan(
        array $data,
        User $kasir
    ): Pesanan {

        // =========================================================================
        // [KEYWORD UKK: ATOMISITAS TRANSAKSI (ACID - DB::transaction)]
        // Menjamin seluruh proses (simpan header, detail, potong stok) berhasil utuh.
        // Jika ada satu langkah gagal atau error, seluruh database di-ROLLBACK otomatis.
        // =========================================================================
        return DB::transaction(function () use ($data, $kasir) {

            $daftarMenu = [];
            $kebutuhanBahan = [];
            $subtotal = 0;

            /*
             * ---------------------------------------------------------------------
             * LANGKAH 1: Validasi Menu & Agregasi Kebutuhan Bahan
             * ---------------------------------------------------------------------
             */
            foreach ($data['items'] as $item) {

                // [KEYWORD UKK: PESSIMISTIC LOCKING MENU]
                // Mengunci baris menu agar tidak diedit saat sedang dipesan kasir.
                $menu = Menu::query()
                    ->with('detailResep')
                    ->lockForUpdate()
                    ->findOrFail($item['menu_id']);

                $jumlahPorsi = (int) $item['jumlah'];

                // [KEYWORD UKK: VALIDASI MENU AKTIF]
                // Menu yang dinonaktifkan manajer dilarang keras dipesan.
                if (!$menu->status_aktif) {
                    throw ValidationException::withMessages([
                        'menu' => "Menu {$menu->nama} sedang nonaktif.",
                    ]);
                }

                // [KEYWORD UKK: VALIDASI RESEP LENGKAP]
                // Menu seperti Latte/Mocha dari CSV yang bahannya belum lengkap ditolak di sini.
                if (
                    !$menu->resep_lengkap ||
                    $menu->detailResep->isEmpty()
                ) {
                    throw ValidationException::withMessages([
                        'resep' => "Resep menu {$menu->nama} belum lengkap.",
                    ]);
                }

                $detailResep = $menu->detailResep;
                $harga = (float) $menu->harga_jual;
                $subtotalItem = $harga * $jumlahPorsi;
                $subtotal += $subtotalItem;

                $daftarMenu[] = [
                    'menu' => $menu,
                    'jumlah' => $jumlahPorsi,
                    'harga' => $harga,
                    'subtotal' => $subtotalItem,
                    'resep' => $detailResep,
                ];

                // =====================================================================
                // [KEYWORD UKK: AGREGASI KEBUTUHAN BAHAN GABUNGAN]
                // Menjumlahkan kebutuhan bahan dari beberapa menu (misal: Susu di Latte + Cappuccino).
                // Mencegah celah stok minus jika dicek per menu secara terpisah.
                // =====================================================================
                foreach ($detailResep as $resep) {
                    $bahanId = $resep->bahan_baku_id;
                    $jumlahDigunakan = (float) $resep->jumlah_per_porsi * $jumlahPorsi;

                    $kebutuhanBahan[$bahanId] =
                        ($kebutuhanBahan[$bahanId] ?? 0) + $jumlahDigunakan;
                }
            }

            /*
             * ---------------------------------------------------------------------
             * LANGKAH 2: Kunci Baris Bahan Baku (Pessimistic Locking)
             * ---------------------------------------------------------------------
             * [KEYWORD UKK: DEADLOCK PREVENTION (orderBy('id'))]
             * Mengurutkan ID bahan baku sebelum dikunci agar tidak saling tunggu (Deadlock).
             * [KEYWORD UKK: RACE CONDITION PREVENTION (lockForUpdate())]
             * Mencegah 2 kasir memakai stok bahan yang sama pada detik yang sama.
             */
            $daftarBahan = BahanBaku::query()
                ->whereIn('id', array_keys($kebutuhanBahan))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            /*
             * ---------------------------------------------------------------------
             * LANGKAH 3: Validasi Kecukupan Stok Fisik di Gudang
             * ---------------------------------------------------------------------
             * [KEYWORD UKK: PENCEGAHAN STOK MINUS]
             * Memastikan jumlah_stok >= total kebutuhan gabungan.
             * Jika kurang, pesanan ditolak dan stok TIDAK berubah sama sekali.
             */
            $bahanKurang = [];

            foreach ($kebutuhanBahan as $bahanId => $dibutuhkan) {
                $bahan = $daftarBahan->get($bahanId);

                if (
                    !$bahan ||
                    (float) $bahan->jumlah_stok < $dibutuhkan
                ) {
                    $bahanKurang[] = $bahan
                        ? $bahan->nama . ' (tersedia: '
                            . $bahan->jumlah_stok . ', dibutuhkan: '
                            . $dibutuhkan . ' ' . $bahan->satuan_dasar . ')'
                        : "Bahan dengan ID {$bahanId} tidak ditemukan.";
                }
            }

            if (!empty($bahanKurang)) {
                throw ValidationException::withMessages([
                    'stok' => [
                        'Pesanan ditolak. Stok bahan tidak mencukupi: '
                        . implode('; ', $bahanKurang),
                    ],
                ]);
            }

            /*
             * ---------------------------------------------------------------------
             * LANGKAH 4: Perhitungan Biaya Layanan 5% & Pajak 10%
             * ---------------------------------------------------------------------
             * [KEYWORD UKK: DASAR PENGENAAN PAJAK (DPP)]
             * Biaya Layanan = 5% dari subtotal.
             * Pajak Restoran = 10% dari (subtotal + biaya layanan).
             */
            $biayaLayanan = round($subtotal * 0.05, 2);
            $pajak = round(($subtotal + $biayaLayanan) * 0.10, 2);
            $total = $subtotal + $biayaLayanan + $pajak;

            /*
             * ---------------------------------------------------------------------
             * LANGKAH 5: Simpan Data Utama Pesanan (Header)
             * ---------------------------------------------------------------------
             * [KEYWORD UKK: FORMAT NOMOR PESANAN UNIK] (PSN-TanggalJam-Acak)
             * [KEYWORD UKK: STATUS AWAL PESANAN] Selalu dimulai dari 'baru'.
             */
            $pesanan = Pesanan::create([
                'nomor_pesanan' => 'PSN-' . now()->format('YmdHis')
                    . '-' . Str::upper(Str::random(4)),
                'dibuat_oleh_pengguna_id' => $kasir->id,
                'nomor_meja' => $data['nomor_meja'] ?? null,
                'metode_pembayaran' => $data['metode_pembayaran'],
                'status' => 'baru',
                'subtotal' => $subtotal,
                'biaya_layanan' => $biayaLayanan,
                'jumlah_pajak' => $pajak,
                'total_harga' => $total,
            ]);

            /*
             * ---------------------------------------------------------------------
             * LANGKAH 6: Simpan Detail Pesanan & Jejak Pemakaian Bahan
             * ---------------------------------------------------------------------
             */
            foreach ($daftarMenu as $item) {

                // =================================================================
                // [KEYWORD UKK: SNAPSHOT DATA HISTORIS]
                // Menyimpan nama dan harga saat transaksi terjadi.
                // Jika harga master menu diubah Manajer esok hari, nota lama tidak berubah.
                // =================================================================
                $detail = DetailPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'menu_id' => $item['menu']->id,
                    'nama_menu_saat_pemesanan' => $item['menu']->nama,
                    'jumlah' => $item['jumlah'],
                    'harga_satuan_saat_pemesanan' => $item['harga'],
                    'subtotal' => $item['subtotal'],
                ]);

                // =================================================================
                // [KEYWORD UKK: AUDIT TRAIL PEMAKAIAN BAHAN BAKU]
                // Mencatat gram/ml bahan yang dipakai ke tabel detail_bahan_pesanan.
                // Kunci utama agar pembatalan pesanan tahu persis takaran bahan yang harus dibalikkan.
                // =================================================================
                foreach ($item['resep'] as $resep) {
                    $jumlahDigunakan = (float) $resep->jumlah_per_porsi * $item['jumlah'];

                    DetailBahanPesanan::create([
                        'detail_pesanan_id' => $detail->id,
                        'bahan_baku_id' => $resep->bahan_baku_id,
                        'jumlah_digunakan' => $jumlahDigunakan,
                    ]);
                }
            }

            /*
             * ---------------------------------------------------------------------
             * LANGKAH 7: Pemotongan Stok Bahan Baku di Gudang
             * ---------------------------------------------------------------------
             * [KEYWORD UKK: ATOMIC DECREMENT]
             * Stok bahan dipotong secara langsung di level database setelah seluruh validasi aman.
             */
            foreach ($kebutuhanBahan as $bahanId => $jumlah) {
                $daftarBahan->get($bahanId)->decrement(
                    'jumlah_stok',
                    $jumlah
                );
            }

            return $pesanan->load([
                'detailPesanan.menu',
                'detailPesanan.detailBahanPesanan.bahanBaku',
                'dibuatOlehPengguna',
            ]);
        });
    }


    /**
     * Mengubah status pesanan secara berurutan.
     *
     * [KEYWORD UKK: STATE MACHINE TRANSISI STATUS]
     * Menegakkan alur linier yang ketat: baru -> diproses -> selesai.
     * Mencegah lompatan ilegal (misal langsung baru -> selesai, atau selesai -> baru).
     */
    public function ubahStatus(
        Pesanan $pesanan,
        string $statusBaru
    ): Pesanan {

        return DB::transaction(function () use ($pesanan, $statusBaru) {

            $pesanan = Pesanan::query()
                ->lockForUpdate()
                ->findOrFail($pesanan->id);

            $statusSaatIni = $pesanan->status;

            // [KEYWORD UKK: MATRIX TRANSISI YANG DIIZINKAN]
            $transisiDiizinkan = [
                'baru' => 'diproses',
                'diproses' => 'selesai',
            ];

            if (
                !isset($transisiDiizinkan[$statusSaatIni]) ||
                $transisiDiizinkan[$statusSaatIni] !== $statusBaru
            ) {
                throw ValidationException::withMessages([
                    'status' => 'Perubahan status pesanan tidak diizinkan.',
                ]);
            }

            $pesanan->update([
                'status' => $statusBaru,
            ]);

            return $pesanan->refresh();
        });
    }


    /**
     * Membatalkan pesanan dan mengembalikan stok ke gudang.
     *
     * [KEYWORD UKK: ATURAN PEMBATALAN PESANAN]
     * Hanya boleh dibatalkan jika status masih 'baru'.
     * Jika sudah 'diproses'/'selesai', pesanan tidak boleh dibatalkan demi integritas dapur.
     */
    public function batalkanPesanan(Pesanan $pesanan): Pesanan
    {
        return DB::transaction(function () use ($pesanan) {

            $pesanan = Pesanan::query()
                ->lockForUpdate()
                ->findOrFail($pesanan->id);

            // [KEYWORD UKK: CEGAH DOUBLE CANCELLATION]
            // Jika pesanan sudah dibatalkan atau diproses, tolak keras.
            if ($pesanan->status !== 'baru') {
                throw ValidationException::withMessages([
                    'pesanan' => 'Pesanan yang sudah diproses tidak dapat dibatalkan.',
                ]);
            }

            $detailPesanan = DetailPesanan::query()
                ->where('pesanan_id', $pesanan->id)
                ->get();

            $detailIds = $detailPesanan->pluck('id');

            // [KEYWORD UKK: AMBIL JEJAK BAHAN DARI DETAIL_BAHAN_PESANAN]
            $detailBahan = DetailBahanPesanan::query()
                ->whereIn('detail_pesanan_id', $detailIds)
                ->get();

            /*
             * Gabungkan jumlah bahan yang harus dikembalikan.
             */
            $stokDikembalikan = [];

            foreach ($detailBahan as $detail) {
                $bahanId = $detail->bahan_baku_id;
                $stokDikembalikan[$bahanId] =
                    ($stokDikembalikan[$bahanId] ?? 0)
                    + (float) $detail->jumlah_digunakan;
            }

            $daftarBahan = BahanBaku::query()
                ->whereIn('id', array_keys($stokDikembalikan))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // =====================================================================
            // [KEYWORD UKK: RESTORE / INCREMENT STOK KE GUDANG]
            // Mengembalikan stok persis sejumlah yang dulu terpakai.
            // =====================================================================
            foreach ($stokDikembalikan as $bahanId => $jumlah) {
                $bahan = $daftarBahan->get($bahanId);

                if (!$bahan) {
                    throw ValidationException::withMessages([
                        'stok' => "Bahan ID {$bahanId} tidak ditemukan.",
                    ]);
                }

                $bahan->increment('jumlah_stok', $jumlah);
            }

            // Ubah status pesanan menjadi 'dibatalkan'
            $pesanan->update([
                'status' => 'dibatalkan',
            ]);

            return $pesanan->refresh();
        });
    }
}
