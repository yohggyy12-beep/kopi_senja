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
     * Signature console command: mendukung pemanggilan 'kedai:import-csv' dan 'app:import-kedai-csv'.
     *
     * @var string
     */
    protected $signature = 'kedai:import-csv {--dir= : Direktori kustom file CSV}';

    /**
     * Alias nama command.
     *
     * @var array<int, string>
     */
    protected $aliases = ['app:import-kedai-csv', 'import:kedai'];

    /**
     * Deskripsi command.
     *
     * @var string
     */
    protected $description = 'Mengimpor data awal bahan baku, menu, dan resep dari file CSV ke database';

    /**
     * Eksekusi perintah console.
     */
    public function handle(): int
    {
        $this->info('=== Memulai Import Data CSV Kedai Kopi Senja (Fase 04) ===');

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

        // Siapkan direktori normalized dan reports
        $normalizedDir = database_path('import/normalized');
        $reportsDir    = database_path('import/reports');
        if (!is_dir($normalizedDir)) {
            mkdir($normalizedDir, 0755, true);
        }
        if (!is_dir($reportsDir)) {
            mkdir($reportsDir, 0755, true);
        }

        $bahanRows = $this->readCsv($bahanPath);
        $menuRows  = $this->readCsv($menuPath);
        $resepRows = $this->readCsv($resepPath);

        $this->info('Ditemukan: ' . count($bahanRows) . ' baris bahan, ' . count($menuRows) . ' baris menu, ' . count($resepRows) . ' baris resep.');

        $report = [
            'bahan_count' => 0,
            'menu_count'  => 0,
            'resep_count' => 0,
            'skipped_rows' => [],
        ];

        $normalizedBahan = [];
        $normalizedMenu  = [];
        $normalizedResep = [];

        DB::transaction(function () use (
            $bahanRows,
            $menuRows,
            $resepRows,
            &$report,
            &$normalizedBahan,
            &$normalizedMenu,
            &$normalizedResep
        ) {
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
                        'nama'                   => trim($row['nama_bahan']),
                        'kode_pemasok'           => !empty($row['kode']) ? trim($row['kode']) : null,
                        'satuan_dasar'           => $converted['base_unit'],
                        'jumlah_stok'            => $converted['quantity'],
                        'stok_minimum'           => 0,
                        'harga_per_satuan_dasar' => $price,
                        'tanggal_pembaruan_harga_pemasok' => now(),
                    ]
                );

                $normalizedBahan[] = [
                    'kode'                   => $row['kode'] ?? '',
                    'nama'                   => trim($row['nama_bahan']),
                    'nama_normalisasi'       => $normalizedName,
                    'stok_awal'              => $converted['quantity'],
                    'satuan_dasar'           => $converted['base_unit'],
                    'harga_per_satuan_dasar' => $price,
                ];

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
                        'kategori'      => trim($row['kategori']),
                        'harga_jual'    => (float) $row['harga_jual'],
                        'status_aktif'  => true,
                        // Awalnya tandai resep belum lengkap sampai semua bahan resep berhasil dipetakan
                        'resep_lengkap' => false,
                    ]
                );

                $kodeMenu = trim($row['kode']);
                $menuMap[$kodeMenu] = $menu;

                $normalizedMenu[] = [
                    'kode'       => $kodeMenu,
                    'nama'       => trim($row['nama_menu']),
                    'kategori'   => trim($row['kategori']),
                    'harga_jual' => (float) $row['harga_jual'],
                ];

                $report['menu_count']++;
            }

            // ========================================================
            // C. Simpan Detail Resep
            // ========================================================
            $this->line('[3/3] Menyimpan detail resep...');

            $menuRecipeCount = [];
            $incompleteMenus = [];

            // Line counter dimulai dari baris 2 (baris 1 adalah header)
            $lineNumber = 1;

            foreach ($resepRows as $row) {
                $lineNumber++;
                $kodeMenu     = trim($row['kode_menu'] ?? '');
                $namaBahanRaw = trim($row['nama_bahan'] ?? '');
                $takaranRaw   = (float) ($row['takaran'] ?? 0);
                $satuanRaw    = trim($row['satuan'] ?? '');

                $menu = $menuMap[$kodeMenu] ?? null;
                if (!$menu) {
                    $incompleteMenus[$kodeMenu] = true;
                    $report['skipped_rows'][] = [
                        'nama_file'    => 'resep.csv',
                        'nomor_baris'  => $lineNumber,
                        'kode_menu'    => $kodeMenu,
                        'nama_bahan'   => $namaBahanRaw,
                        'alasan'       => "Kode menu '{$kodeMenu}' tidak ditemukan di menu.csv",
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
                        'nama_file'    => 'resep.csv',
                        'nomor_baris'  => $lineNumber,
                        'kode_menu'    => $kodeMenu,
                        'nama_bahan'   => $namaBahanRaw,
                        'alasan'       => "Bahan baku '{$namaBahanRaw}' tidak ditemukan di database",
                    ];

                    $normalizedResep[] = [
                        'kode_menu'                => $kodeMenu,
                        'nama_bahan_asli'          => $namaBahanRaw,
                        'nama_bahan_normalisasi'   => $resolvedName,
                        'takaran'                  => $takaranRaw,
                        'satuan_dasar'             => $satuanRaw,
                        'status'                   => 'Dilewati: Bahan Tidak Ditemukan',
                    ];
                    continue;
                }

                // 4. Periksa kesesuaian satuan dengan satuan dasar bahan
                $converted = CsvValueNormalizer::toBaseUnit($takaranRaw, $satuanRaw);

                if ($converted['base_unit'] !== $bahan->satuan_dasar) {
                    $incompleteMenus[$menu->id] = true;

                    $report['skipped_rows'][] = [
                        'nama_file'    => 'resep.csv',
                        'nomor_baris'  => $lineNumber,
                        'kode_menu'    => $kodeMenu,
                        'nama_bahan'   => $namaBahanRaw,
                        'alasan'       => "Satuan takaran ({$converted['base_unit']}) tidak cocok dengan satuan dasar bahan ({$bahan->satuan_dasar})",
                    ];

                    $normalizedResep[] = [
                        'kode_menu'                => $kodeMenu,
                        'nama_bahan_asli'          => $namaBahanRaw,
                        'nama_bahan_normalisasi'   => $resolvedName,
                        'takaran'                  => $converted['quantity'],
                        'satuan_dasar'             => $converted['base_unit'],
                        'status'                   => 'Dilewati: Satuan Tidak Cocok',
                    ];
                    continue;
                }

                // 5. Simpan detail resep menggunakan pasangan unik menu dan bahan
                DetailResep::updateOrCreate(
                    [
                        'menu_id'       => $menu->id,
                        'bahan_baku_id' => $bahan->id,
                    ],
                    [
                        'jumlah_per_porsi' => $converted['quantity'],
                    ]
                );

                $normalizedResep[] = [
                    'kode_menu'                => $kodeMenu,
                    'nama_bahan_asli'          => $namaBahanRaw,
                    'nama_bahan_normalisasi'   => $resolvedName,
                    'takaran'                  => $converted['quantity'],
                    'satuan_dasar'             => $converted['base_unit'],
                    'status'                   => 'Tersimpan',
                ];

                $menuRecipeCount[$menu->id] = ($menuRecipeCount[$menu->id] ?? 0) + 1;
                $report['resep_count']++;
            }

            // Evaluasi status resep_lengkap untuk setiap menu
            foreach ($menuMap as $kode => $menu) {
                $hasItems = ($menuRecipeCount[$menu->id] ?? 0) > 0;
                $isIncomplete = isset($incompleteMenus[$menu->id]);

                $isLengkap = $hasItems && !$isIncomplete;

                $menu->update(['resep_lengkap' => $isLengkap]);
            }
        });

        // ========================================================
        // Tulis File Normalized & Reports
        // ========================================================
        $this->writeCsv(
            $normalizedDir . DIRECTORY_SEPARATOR . 'bahan-normalized.csv',
            ['kode', 'nama', 'nama_normalisasi', 'stok_awal', 'satuan_dasar', 'harga_per_satuan_dasar'],
            $normalizedBahan
        );

        $this->writeCsv(
            $normalizedDir . DIRECTORY_SEPARATOR . 'menu-normalized.csv',
            ['kode', 'nama', 'kategori', 'harga_jual'],
            $normalizedMenu
        );

        $this->writeCsv(
            $normalizedDir . DIRECTORY_SEPARATOR . 'resep-normalized.csv',
            ['kode_menu', 'nama_bahan_asli', 'nama_bahan_normalisasi', 'takaran', 'satuan_dasar', 'status'],
            $normalizedResep
        );

        $this->writeCsv(
            $reportsDir . DIRECTORY_SEPARATOR . 'import-report.csv',
            ['nama_file', 'nomor_baris', 'kode_menu', 'nama_bahan', 'alasan_dilewati'],
            array_map(function ($row) {
                return [
                    'nama_file'       => $row['nama_file'],
                    'nomor_baris'     => $row['nomor_baris'],
                    'kode_menu'       => $row['kode_menu'],
                    'nama_bahan'      => $row['nama_bahan'],
                    'alasan_dilewati' => $row['alasan'],
                ];
            }, $report['skipped_rows'])
        );

        // ========================================================
        // Tampilkan Laporan Hasil Import
        // ========================================================
        $this->newLine();
        $this->info('Import CSV berhasil diselesaikan!');
        $this->table(
            ['Komponen', 'Jumlah Tersimpan'],
            [
                ['Bahan Baku', $report['bahan_count']],
                ['Menu', $report['menu_count']],
                ['Detail Resep Baris (Berhasil Dipetakan)', $report['resep_count']],
            ]
        );

        if (!empty($report['skipped_rows'])) {
            $this->warn("\nLaporan Baris Resep yang Dilewati (Bahan Tidak Dikenal):");
            $this->table(
                ['File', 'Baris', 'Kode Menu', 'Bahan Tertera', 'Alasan'],
                array_map(function ($row) {
                    return [
                        $row['nama_file'],
                        $row['nomor_baris'],
                        $row['kode_menu'],
                        $row['nama_bahan'],
                        $row['alasan'],
                    ];
                }, $report['skipped_rows'])
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
                if ($header === null) {
                    // Bersihkan UTF-8 BOM jika ada
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
    /**
     * Menulis data array asosiatif ke file CSV.
     */
    protected function writeCsv(string $filePath, array $headers, array $rows): void
    {
        if (($handle = fopen($filePath, 'w')) !== false) {
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, array_values($row));
            }
            fclose($handle);
        }
    }
}
