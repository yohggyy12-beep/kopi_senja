<?php

namespace Tests\Feature;

use App\Models\BahanBaku;
use App\Models\DetailResep;
use App\Models\Menu;
<<<<<<< HEAD
use Database\Seeders\DemoStateSeeder;
=======
>>>>>>> fa7bc80f4ea61204ec4726db2b17aae0029e23a2
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportKedaiCsvTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
    public function test_import_kedai_csv_and_demo_state_matches_phase_04_checklist(): void
    {
        // 1. Jalankan command import CSV
        $this->artisan('kedai:import-csv')
            ->expectsOutputToContain('Import CSV berhasil diselesaikan!')
            ->assertSuccessful();

        // Pemeriksaan 1: Jumlah Bahan Baku = 20 data
        $this->assertEquals(20, BahanBaku::count());

        // Pemeriksaan 2: Jumlah Menu = 14 data
        $this->assertEquals(14, Menu::count());

        // Pemeriksaan 3: Detail resep yang berhasil dipetakan = 63 data
        $this->assertEquals(63, DetailResep::count());

        // Pemeriksaan 4 & 5: Konversi stok & harga Biji Kopi Arabika
        // 5 kg -> 5000 g, Rp 180.000 / 1000 -> Rp 180 / g, stok minimum 0
        $this->assertDatabaseHas('bahan_baku', [
            'nama_normalisasi'       => 'biji kopi arabika',
            'satuan_dasar'           => 'g',
            'jumlah_stok'            => 5000,
            'harga_per_satuan_dasar' => 180,
            'stok_minimum'           => 0,
        ]);

        // Konversi Susu UHT: 20 liter -> 20000 ml
        $this->assertDatabaseHas('bahan_baku', [
            'nama_normalisasi'       => 'susu uht',
            'satuan_dasar'           => 'ml',
            'jumlah_stok'            => 20000,
            'harga_per_satuan_dasar' => 18,
            'stok_minimum'           => 0,
        ]);

        // Pemeriksaan 6: Alias Arabica dipetakan ke Arabika (Menu Flat White lengkap)
        $this->assertDatabaseHas('menus', [
            'nama'          => 'Flat White',
            'resep_lengkap' => true,
        ]);

        // Pemeriksaan 7: Resep Latte belum lengkap (karena Susu oat tidak ada)
        $this->assertDatabaseHas('menus', [
            'nama'          => 'Latte',
            'resep_lengkap' => false,
        ]);

        // Pemeriksaan 8: Resep Mocha belum lengkap (karena Sirup vanila tidak ada)
        $this->assertDatabaseHas('menus', [
            'nama'          => 'Mocha',
            'resep_lengkap' => false,
        ]);

        // Pemeriksaan 9: Jalankan seeder demo -> stok Susu UHT menjadi 300 ml (0.3 liter)
        $this->seed(DemoStateSeeder::class);

        $susu = BahanBaku::where('nama_normalisasi', 'susu uht')->first();
        $this->assertNotNull($susu);
        $this->assertEquals(300, $susu->jumlah_stok);

        // Menjalankan seeder ulang tidak menambah stok (tetap 300 ml)
        $this->seed(DemoStateSeeder::class);
        $this->assertEquals(300, $susu->fresh()->jumlah_stok);

        // Pemeriksaan 10: Import ulang tidak menggandakan data
        $this->artisan('kedai:import-csv')->assertSuccessful();
        $this->assertEquals(20, BahanBaku::count());
        $this->assertEquals(14, Menu::count());
        $this->assertEquals(63, DetailResep::count());

        // Pemeriksaan 11: File normalized dan reports berhasil dibuat
        $this->assertFileExists(database_path('import/normalized/bahan-normalized.csv'));
        $this->assertFileExists(database_path('import/normalized/menu-normalized.csv'));
        $this->assertFileExists(database_path('import/normalized/resep-normalized.csv'));
        $this->assertFileExists(database_path('import/reports/import-report.csv'));

        // Cek isi laporan memuat Susu oat dan Sirup vanila
        $reportContent = file_get_contents(database_path('import/reports/import-report.csv'));
        $this->assertStringContainsString('Susu oat', $reportContent);
        $this->assertStringContainsString('Sirup vanila', $reportContent);
        $this->assertStringContainsString('resep.csv', $reportContent);
=======
    public function test_import_kedai_csv_command_imports_data_accurately(): void
    {
        $this->artisan('app:import-kedai-csv')
            ->expectsOutputToContain('Import berhasil diselesaikan!')
            ->assertSuccessful();

        // 1. Verifikasi Bahan Baku tersimpan dan terkonversi
        // Biji kopi arabika: stok 5 kg => 5000 g, harga 180000 / 1000 => 180
        $this->assertDatabaseHas('bahan_baku', [
            'nama_normalisasi' => 'biji kopi arabika',
            'satuan_dasar' => 'g',
            'jumlah_stok' => 5000,
            'harga_per_satuan_dasar' => 180,
            'stok_minimum' => 0,
        ]);

        // Susu UHT: stok 20 liter => 20000 ml
        $this->assertDatabaseHas('bahan_baku', [
            'nama_normalisasi' => 'susu uht',
            'satuan_dasar' => 'ml',
            'jumlah_stok' => 20000,
        ]);

        // 2. Verifikasi Menu tersimpan
        $this->assertDatabaseHas('menus', [
            'nama' => 'Espresso',
            'kategori' => 'Minuman',
            'harga_jual' => 18000,
            'resep_lengkap' => true,
        ]);

        // Flat White menggunakan alias 'Biji kopi arabica' => resep lengkap harus true
        $this->assertDatabaseHas('menus', [
            'nama' => 'Flat White',
            'resep_lengkap' => true,
        ]);

        // Latte memiliki bahan yang belum ada ('Susu oat') => resep lengkap harus false
        $this->assertDatabaseHas('menus', [
            'nama' => 'Latte',
            'resep_lengkap' => false,
        ]);

        // Mocha memiliki bahan yang belum ada ('Sirup vanila') => resep lengkap harus false
        $this->assertDatabaseHas('menus', [
            'nama' => 'Mocha',
            'resep_lengkap' => false,
        ]);

        // 3. Verifikasi Detail Resep tersimpan
        $espresso = Menu::where('nama', 'Espresso')->first();
        $this->assertNotNull($espresso);
        $this->assertTrue($espresso->detailResep()->exists());
>>>>>>> fa7bc80f4ea61204ec4726db2b17aae0029e23a2
    }
}
