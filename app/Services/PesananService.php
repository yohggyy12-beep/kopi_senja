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
     */
    public function buatPesanan(
        array $data,
        User $kasir
    ): Pesanan {

        return DB::transaction(function () use ($data, $kasir) {

            $daftarMenu = [];
            $kebutuhanBahan = [];
            $subtotal = 0;

            /*
             * LANGKAH 1:
             * Periksa menu dan kumpulkan seluruh
             * kebutuhan bahan dari semua menu.
             */
            foreach ($data['items'] as $item) {

                $menu = Menu::query()
                    ->with('detailResep')
                    ->lockForUpdate()
                    ->findOrFail($item['menu_id']);

                $jumlahPorsi = (int) $item['jumlah'];

                // Menu nonaktif tidak boleh dipesan.
                if (!$menu->is_aktif) {
                    throw ValidationException::withMessages([
                        'menu' => "Menu {$menu->nama} sedang nonaktif.",
                    ]);
                }

                // Menu harus mempunyai resep lengkap.
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

                /*
                 * Kumpulkan kebutuhan bahan.
                 * Jika bahan sama dipakai beberapa menu,
                 * jumlahnya akan digabung.
                 */
                foreach ($detailResep as $resep) {

                    $bahanId = $resep->bahan_baku_id;

                    $jumlahDigunakan =
                        (float) $resep->jumlah * $jumlahPorsi;

                    $kebutuhanBahan[$bahanId] =
                        ($kebutuhanBahan[$bahanId] ?? 0)
                        + $jumlahDigunakan;
                }
            }

            /*
             * LANGKAH 2:
             * Kunci seluruh baris bahan baku
             * untuk mencegah dua pesanan memakai stok
             * yang sama secara bersamaan.
             */
            $daftarBahan = BahanBaku::query()
                ->whereIn('id', array_keys($kebutuhanBahan))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            /*
             * LANGKAH 3:
             * Periksa apakah seluruh stok mencukupi.
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
             * LANGKAH 4:
             * Hitung biaya sesuai aturan UKK.
             */
            $biayaLayanan = round($subtotal * 0.05, 2);

            $pajak = round(
                ($subtotal + $biayaLayanan) * 0.10,
                2
            );

            $total = $subtotal + $biayaLayanan + $pajak;

            /*
             * LANGKAH 5:
             * Simpan data utama pesanan.
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
             * LANGKAH 6:
             * Simpan setiap menu sebagai detail pesanan.
             * Nama dan harga disimpan sebagai snapshot.
             */
            foreach ($daftarMenu as $item) {

                $detail = DetailPesanan::create([
                    'pesanan_id' => $pesanan->id,

                    'menu_id' => $item['menu']->id,

                    'nama_menu_saat_pemesanan' => $item['menu']->nama,

                    'jumlah' => $item['jumlah'],

                    'harga_satuan_saat_pemesanan' => $item['harga'],

                    'subtotal' => $item['subtotal'],
                ]);

                /*
                 * Simpan bahan yang digunakan untuk
                 * setiap detail menu.
                 */
                foreach ($item['resep'] as $resep) {

                    $jumlahDigunakan =
                        (float) $resep->jumlah * $item['jumlah'];

                    DetailBahanPesanan::create([
                        'detail_pesanan_id' => $detail->id,

                        'bahan_baku_id' => $resep->bahan_baku_id,

                        'jumlah_digunakan' => $jumlahDigunakan,
                    ]);
                }
            }

            /*
             * LANGKAH 7:
             * Setelah semua validasi lolos,
             * kurangi stok berdasarkan total kebutuhan.
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
     * Membatalkan pesanan dan mengembalikan stok.
     */
    public function batalkanPesanan(Pesanan $pesanan): Pesanan
    {
        return DB::transaction(function () use ($pesanan) {

            $pesanan = Pesanan::query()
                ->lockForUpdate()
                ->findOrFail($pesanan->id);

            // Pembatalan hanya boleh saat Baru.
            if ($pesanan->status !== 'baru') {
                throw ValidationException::withMessages([
                    'pesanan' => 'Pesanan yang sudah diproses tidak dapat dibatalkan.',
                ]);
            }

            $detailPesanan = DetailPesanan::query()
                ->where('pesanan_id', $pesanan->id)
                ->get();

            $detailIds = $detailPesanan->pluck('id');

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

            /*
             * Kembalikan stok sesuai catatan penggunaan
             * pada saat pesanan dibuat.
             */
            foreach ($stokDikembalikan as $bahanId => $jumlah) {

                $bahan = $daftarBahan->get($bahanId);

                if (!$bahan) {
                    throw ValidationException::withMessages([
                        'stok' => "Bahan ID {$bahanId} tidak ditemukan.",
                    ]);
                }

                $bahan->increment('jumlah_stok', $jumlah);
            }

            // Ubah status setelah stok berhasil dikembalikan.
            $pesanan->update([
                'status' => 'dibatalkan',
            ]);

            return $pesanan->refresh();
        });
    }
}
