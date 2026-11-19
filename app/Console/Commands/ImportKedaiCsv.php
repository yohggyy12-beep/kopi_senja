<?php

namespace App\Console\Commands;

use App\Models\BahanBaku;
use App\Models\DetailResep;
use App\Models\Menu;
use App\Support\Import\CsvValueNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportKedaiCsv extends Command
{
    /**
     * Nama dan signature perintah console.
     *
     * @var string
     */
    protected $signature = 'app:import-kedai-csv {--dir= : Direktori kustom file CSV}';

    /**
     * Nama alias untuk kemudahan pemanggilan command.
     *
     * @var array<int, string>
     */
    protected $aliases = ['import:kedai'];

    /**
     * Deskripsi perintah console.
     *
     * @var string
     */
    protected $description = 'Mengimpor data bahan baku, menu, dan resep dari file CSV ke database';

    /**
     * Eksekusi perintah console.
     */
    public function handle(): int
    {
        $this->info('=== Memulai Import Data CSV Kedai Kopi Senja ===');

        $baseDir = $this->option('dir')
            ? rtrim($this->option('dir'), '/\\')
            : database_path('import/original');

        $bahanPath = $baseDir . DIRECTORY_SEPARATOR . 'bahan.csv';
        $menuPath  = $baseDir . DIRECTORY_SEPARATOR . 'menu.csv';
        $resepPath = $baseDir . DIRECTORY_SEPARATOR . 'resep.csv';

        // Validasi keberadaan file CSV
        foreach (['Bahan Baku' => $bahanPath, 'Menu' => $menuPath, 'Resep' => $resepPath] as $label => $path) {
            if (!file_exists($path)) {
                $this->error("File {$label} tidak ditemukan di: {$path}");
                return Command::FAILURE;
            }
        }

        $bahanRows = $this->readCsv($bahanPath);
        $menuRows  = $this->readCsv($menuPath);
        $resepRows = $this->readCsv($resepPath);

        $this->info('Ditemukan: ' . count($bahanRows) . ' baris bahan, ' . count($menuRows) . ' baris menu, ' . count($resepRows) . ' baris resep.');

        $report = [
            'bahan_count' => 0,
            'menu_count' => 0,
            'resep_count' => 0,
            'skipped_rows' => [],
        ];

        DB::transaction(function () use ($bahanRows, $menuRows, $resepRows, &$report) {
            // ========================================================
            // A. Simpan Bahan Baku
            // ========================================================
            $this->line("\n[1/3] Menyimpan bahan baku...");

            foreach ($bahanRows as $row) {
                $normalizedName = CsvValueNormalizer::normalizeName(
                    $row['nama_bahan']
                );

                $converted = CsvValueNormalizer::toBaseUnit(
                    (float) $row['stok_awal'],
                    $row['satuan']
                );

                $price = CsvValueNormalizer::pricePerBaseUnit(
                    (float) $row['harga_per_satuan'],
                    $converted['factor']
                );

                BahanBaku::updateOrCreate(
                    ['nama_normalisasi' => $normalizedName],
                    [
                        'nama' => trim($row['nama_bahan']),
                        'kode_pemasok' => !empty($row['kode']) ? trim($row['kode']) : null,
                        'satuan_dasar' => $converted['unit'],
                        'jumlah_stok' => $converted['value'],
                        'stok_minimum' => 0,
                        'harga_per_satuan_dasar' => $price,
                        'tanggal_pembaruan_harga_pemasok' => now(),
                    ]
                );

                $report['bahan_count']++;
            }

            // ========================================================
            // B. Simpan Menu
            // ========================================================
            $this->line('[2/3] Menyimpan menu...');

            // Gunakan kode pada menu.csv sebagai kunci penghubung sementara dengan kode_menu pada resep.csv
            $menuMap = [];

            foreach ($menuRows as $row) {
                $menu = Menu::updateOrCreate(
                    ['nama' => trim($row['nama_menu'])],
                    [
                        'kategori' => trim($row['kategori']),
                        'harga_jual' => (float) $row['harga_jual'],
                        'status_aktif' => true,
                        // Awalnya tandai resep belum lengkap sampai semua bahan resep berhasil dipetakan
                        'resep_lengkap' => false,
                    ]
                );

                $kodeMenu = trim($row['kode']);
                $menuMap[$kodeMenu] = $menu;
                $report['menu_count']++;
            }

            // ========================================================
            // C. Simpan Detail Resep
            // ========================================================
            $this->line('[3/3] Menyimpan detail resep...');

            $menuRecipeCount = [];
            $incompleteMenus = [];

            foreach ($resepRows as $row) {
                $kodeMenu     = trim($row['kode_menu'] ?? '');
                $namaBahanRaw = trim($row['nama_bahan'] ?? '');
                $takaranRaw   = (float) ($row['takaran'] ?? 0);
                $satuanRaw    = trim($row['satuan'] ?? '');

                // Ambil menu dari menuMap
                $menu = $menuMap[$kodeMenu] ?? null;
                if (!$menu) {
                    $report['skipped_rows'][] = [
                        'kode_menu'  => $kodeMenu,
                        'menu'       => 'Tidak Dikenal',
                        'nama_bahan' => $namaBahanRaw,
                        'alasan'     => "Kode menu '{$kodeMenu}' tidak ditemukan di menu.csv",
                    ];
                    continue;
                }

                // 1. Normalisasi nama_bahan
                $normalizedName = CsvValueNormalizer::normalizeName($namaBahanRaw);

                // 2. Cari padanan di daftar alias
                $resolvedName = CsvValueNormalizer::resolveAlias($normalizedName);

                // 3. Cari bahan pada database
                $bahan = BahanBaku::where('nama_normalisasi', $resolvedName)->first();

                // 6. Bila bahan tidak ditemukan, lewati baris dan catat alasan dalam laporan
                if (!$bahan) {
                    // 7. Tandai menu terkait sebagai resep belum lengkap
                    $incompleteMenus[$menu->id] = true;

                    $report['skipped_rows'][] = [
                        'kode_menu'  => $kodeMenu,
                        'menu'       => $menu->nama,
                        'nama_bahan' => $namaBahanRaw,
                        'alasan'     => "Bahan baku '{$namaBahanRaw}' tidak ditemukan di database",
                    ];
                    continue;
                }

                // 4. Periksa kesesuaian satuan dengan satuan dasar bahan
                $converted = CsvValueNormalizer::toBaseUnit($takaranRaw, $satuanRaw);

                if ($converted['unit'] !== $bahan->satuan_dasar) {
                    // Satuan tidak cocok dengan satuan dasar bahan
                    $incompleteMenus[$menu->id] = true;

                    $report['skipped_rows'][] = [
                        'kode_menu'  => $kodeMenu,
                        'menu'       => $menu->nama,
                        'nama_bahan' => $namaBahanRaw,
                        'alasan'     => "Satuan takaran ({$converted['unit']}) tidak cocok dengan satuan dasar bahan ({$bahan->satuan_dasar})",
                    ];
                    continue;
                }

                // 5. Simpan detail resep menggunakan pasangan unik menu dan bahan
                DetailResep::updateOrCreate(
                    [
                        'menu_id' => $menu->id,
                        'bahan_baku_id' => $bahan->id,
                    ],
                    [
                        'jumlah_per_porsi' => $converted['value'],
                    ]
                );

                $menuRecipeCount[$menu->id] = ($menuRecipeCount[$menu->id] ?? 0) + 1;
                $report['resep_count']++;
            }

            // Evaluasi status resep_lengkap untuk setiap menu
            foreach ($menuMap as $kode => $menu) {
                // Menu lengkap jika punya resep dan tidak ada bahan yang gagal dipetakan
                $hasItems = ($menuRecipeCount[$menu->id] ?? 0) > 0;
                $isIncomplete = isset($incompleteMenus[$menu->id]);

                $isLengkap = $hasItems && !$isIncomplete;

                $menu->update(['resep_lengkap' => $isLengkap]);
            }
        });

        // ========================================================
        // Tampilkan Laporan Hasil Import
        // ========================================================
        $this->newLine();
        $this->info('Import berhasil diselesaikan!');
        $this->table(
            ['Komponen', 'Jumlah Tersimpan'],
            [
                ['Bahan Baku', $report['bahan_count']],
                ['Menu', $report['menu_count']],
                ['Detail Resep Baris', $report['resep_count']],
            ]
        );

        if (!empty($report['skipped_rows'])) {
            $this->warn("\nLaporan Baris Resep yang Dilewati (Bahan Tidak Ditemukan / Tidak Cocok):");
            $this->table(
                ['Kode Menu', 'Nama Menu', 'Bahan Tertera', 'Alasan Dilewati'],
                $report['skipped_rows']
            );
        }

        return Command::SUCCESS;
    }

    /**
     * Membaca file CSV menjadi array asosiatif.
     *
     * @return array<int, array<string, string>>
     */
    protected function readCsv(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("File CSV tidak ditemukan: {$filePath}");
        }

        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = null;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Bersihkan UTF-8 BOM jika ada pada karakter pertama header
                if ($header === null) {
                    $data[0] = preg_replace('/^\x{EF}\x{BB}\x{BF}/u', '', $data[0]);
                    $header = array_map('trim', $data);
                    continue;
                }

                if (count($data) === count($header)) {
                    $rows[] = array_combine($header, array_map('trim', $data));
                }
            }
            fclose($handle);
        }

        return $rows;
    }
}
