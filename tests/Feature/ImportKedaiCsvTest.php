<?php

namespace Tests\Feature;

use App\Models\BahanBaku;
use App\Models\DetailResep;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportKedaiCsvTest extends TestCase
{
    use RefreshDatabase;

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
    }
}
