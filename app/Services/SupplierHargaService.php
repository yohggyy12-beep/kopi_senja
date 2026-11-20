<?php

namespace App\Services;

use App\Models\BahanBaku;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Throwable;

class SupplierHargaService
{
    /**
     * Mengambil harga terbaru dari Supplier
     * dan memperbarui harga bahan baku lokal.
     */
    public function perbaruiHarga(): array
    {
        $baseUrl = rtrim(
            (string) config('services.supplier.base_url'),
            '/'
        );

        $apiKey = config('services.supplier.api_key');

        // Periksa konfigurasi terlebih dahulu.
        if (empty($baseUrl) || empty($apiKey)) {
            return $this->gunakanHargaTersimpan(
                'Konfigurasi Supplier belum lengkap.'
            );
        }

        try {

            // Hubungi layanan Supplier dari server Laravel.
            $response = Http::acceptJson()
                ->withHeaders([
                    'X-API-Key' => $apiKey,
                ])
                ->connectTimeout(5)
                ->timeout(10)
                ->get($baseUrl . '/harga-bahan');

            // Jika API key salah atau layanan gagal,
            // gunakan harga terakhir di database.
            if (!$response->successful()) {
                return $this->gunakanHargaTersimpan(
                    'Supplier tidak dapat dihubungi atau API key tidak valid.'
                );
            }

            $hasil = $response->json();

            // Pastikan format response benar.
            if (
                !is_array($hasil) ||
                empty($hasil['diperbarui']) ||
                !isset($hasil['data']) ||
                !is_array($hasil['data']) ||
                empty($hasil['data'])
            ) {
                return $this->gunakanHargaTersimpan(
                    'Format data Supplier tidak valid.'
                );
            }

            // Ambil tanggal pembaruan dari Supplier.
            $tanggal = Carbon::parse(
                $hasil['diperbarui']
            )->toDateString();

            $jumlahDiperbarui = 0;
            $jumlahDiabaikan = 0;

            DB::transaction(function () use (
                $hasil,
                $tanggal,
                &$jumlahDiperbarui,
                &$jumlahDiabaikan
            ) {

                foreach ($hasil['data'] as $item) {

                    // Lewati data yang tidak lengkap.
                    if (
                        !isset(
                            $item['kode'],
                            $item['satuan_dasar'],
                            $item['harga_per_satuan']
                        ) ||
                        !is_numeric($item['harga_per_satuan']) ||
                        $item['harga_per_satuan'] < 0
                    ) {
                        $jumlahDiabaikan++;
                        continue;
                    }

                    // Cari bahan berdasarkan kode Supplier.
                    $bahan = BahanBaku::query()
                        ->where('kode_pemasok', $item['kode'])
                        ->lockForUpdate()
                        ->first();

                    // Lewati bahan yang tidak dikenal.
                    if (!$bahan) {
                        $jumlahDiabaikan++;
                        continue;
                    }

                    // Satuan wajib sama.
                    if (
                        $bahan->satuan_dasar !==
                        $item['satuan_dasar']
                    ) {
                        $jumlahDiabaikan++;
                        continue;
                    }

                    // Simpan harga dan tanggal pembaruan.
                    $bahan->update([
                        'harga_per_satuan_dasar' =>
                            $item['harga_per_satuan'],

                        'tanggal_pembaruan_harga_pemasok' =>
                            $tanggal,
                    ]);

                    $jumlahDiperbarui++;
                }
            });

            return [
                'berhasil' => true,
                'sumber' => 'supplier',

                'pesan' =>
                    'Pembaruan harga Supplier selesai.',

                'jumlah_diperbarui' =>
                    $jumlahDiperbarui,

                'jumlah_diabaikan' =>
                    $jumlahDiabaikan,

                'tanggal_pembaruan' =>
                    $tanggal,

                'data' => $this->daftarHargaTersimpan(),
            ];

        } catch (Throwable $error) {

            // Jangan tampilkan error internal atau
            // menghentikan sistem jika API gagal.
            return $this->gunakanHargaTersimpan(
                'Terjadi gangguan saat menghubungi Supplier.'
            );
        }
    }


    /**
     * Menampilkan harga terakhir dari database.
     */
    private function daftarHargaTersimpan(): array
    {
        return BahanBaku::query()
            ->orderBy('nama')
            ->get([
                'id',
                'nama',
                'kode_pemasok',
                'satuan_dasar',
                'harga_per_satuan_dasar',
                'tanggal_pembaruan_harga_pemasok',
            ])
            ->toArray();
    }


    /**
     * Fallback ketika Supplier tidak tersedia.
     */
    private function gunakanHargaTersimpan(
        string $pesan
    ): array {

        return [
            'berhasil' => false,

            'sumber' => 'database',

            'pesan' => $pesan .
                ' Sistem tetap menggunakan harga terakhir yang tersimpan.',

            'data' => $this->daftarHargaTersimpan(),
        ];
    }
}
