<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Support\Import\CsvValueNormalizer;
use Illuminate\Database\Seeder;

class DemoStateSeeder extends Seeder
{
    /**
     * Menetapkan kondisi stok demo untuk pengujian.
     */
    public function run(): void
    {
        $csvPath = config('import.paths.demo_stok', database_path('import/original/seed_stok_demo.csv'));

        if (file_exists($csvPath) && ($handle = fopen($csvPath, 'r')) !== false) {
            $header = null;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if ($header === null) {
                    $header = array_map('trim', $data);
                    continue;
                }

                if (count($data) >= 3) {
                    $namaBahan = trim($data[0]);
                    $jumlah    = (float) trim($data[1]);
                    $satuan    = trim($data[2]);

                    $normalizedName = CsvValueNormalizer::normalizeName($namaBahan);
                    $converted      = CsvValueNormalizer::toBaseUnit($jumlah, $satuan);

                    $bahan = BahanBaku::where('nama_normalisasi', $normalizedName)->first();
                    if ($bahan) {
                        // Menggunakan update() menetapkan stok akhir menjadi 300 ml (bukan increment)
                        $bahan->update([
                            'jumlah_stok' => $converted['quantity'],
                        ]);
                    }
                }
            }
            fclose($handle);
        } else {
            $susu = BahanBaku::where('nama_normalisasi', 'susu uht')->first();
            if ($susu) {
                $susu->update([
                    'jumlah_stok' => 300,
                ]);
            }
        }
    }
}
