<?php

namespace Tests\Feature\Manajer;

use App\Models\User;
use App\Models\Menu;
use App\Models\BahanBaku;
use App\Models\DetailResep;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper untuk membuat menu pengujian.
     */
    private function createMenu(
        array $override = []
    ): Menu {

        return Menu::create(array_merge([

            'nama' => 'Latte',

            'kategori' => 'Minuman',

            'harga_jual' => 25000,

            'status_aktif' => true,

            'resep_lengkap' => false,

        ], $override));

    }

    /**
     * Test 1: Manajer berhasil menambah menu.
     */
    public function test_manager_can_create_menu(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $response = $this
            ->actingAs($manager)
            ->postJson('/manajer/menu', [

                'nama' => 'Latte',

                'kategori' => 'Minuman',

                'harga_jual' => 25000,

            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('menus', [

            'nama' => 'Latte',

            'kategori' => 'Minuman',

            'harga_jual' => 25000,

            'status_aktif' => true,

            'resep_lengkap' => false,

        ]);
    }

    /**
     * Test 2: Kategori yang tidak dikenal ditolak.
     */
    public function test_invalid_category_is_rejected(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $response = $this
            ->actingAs($manager)
            ->postJson('/manajer/menu', [

                'nama' => 'Latte',

                'kategori' => 'Elektronik',

                'harga_jual' => 25000,

            ]);

        $response->assertUnprocessable();

        $response->assertJsonValidationErrors([
            'kategori',
        ]);
    }

    /**
     * Test 3: Harga nol atau negatif ditolak.
     */
    public function test_invalid_price_is_rejected(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $response = $this
            ->actingAs($manager)
            ->postJson('/manajer/menu', [

                'nama' => 'Latte',

                'kategori' => 'Minuman',

                'harga_jual' => -5000,

            ]);

        $response->assertUnprocessable();

        $response->assertJsonValidationErrors([
            'harga_jual',
        ]);
    }

    /**
     * Test 4: Manajer bisa mengubah harga menu.
     */
    public function test_manager_can_update_menu_price(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $menu = $this->createMenu();

        $response = $this
            ->actingAs($manager)
            ->putJson(
                '/manajer/menu/' . $menu->id,
                [
                    'harga_jual' => 30000,
                ]
            );

        $response->assertOk();

        $this->assertDatabaseHas('menus', [

            'id' => $menu->id,

            'harga_jual' => 30000,

        ]);
    }

    /**
     * Test 5: Manajer bisa menonaktifkan menu.
     */
    public function test_manager_can_deactivate_menu(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $menu = $this->createMenu();

        $response = $this
            ->actingAs($manager)
            ->patchJson(
                '/manajer/menu/' . $menu->id,
                [
                    'status_aktif' => false,
                ]
            );

        $response->assertOk();

        $this->assertDatabaseHas('menus', [

            'id' => $menu->id,

            'status_aktif' => false,

        ]);
    }

    /**
     * Test 6: Manajer bisa mengaktifkan kembali menu.
     */
    public function test_manager_can_reactivate_menu(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $menu = $this->createMenu([
            'status_aktif' => false,
        ]);

        $response = $this
            ->actingAs($manager)
            ->patchJson(
                '/manajer/menu/' . $menu->id,
                [
                    'status_aktif' => true,
                ]
            );

        $response->assertOk();

        $this->assertDatabaseHas('menus', [

            'id' => $menu->id,

            'status_aktif' => true,

        ]);
    }

    /**
     * Test 7: Daftar menu dapat difilter berdasarkan kategori.
     */
    public function test_manager_can_filter_menu_by_category(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $this->createMenu([
            'nama' => 'Latte',
            'kategori' => 'Minuman',
        ]);

        $this->createMenu([
            'nama' => 'Roti Bakar',
            'kategori' => 'Makanan',
        ]);

        $response = $this
            ->actingAs($manager)
            ->getJson(
                '/manajer/menu?kategori=Minuman'
            );

        $response->assertOk();

        $response->assertJsonFragment([
            'nama' => 'Latte',
        ]);

        $response->assertJsonMissing([
            'nama' => 'Roti Bakar',
        ]);
    }

    /**
     * Test 8: Kasir tidak boleh mengelola menu.
     */
    public function test_kasir_cannot_manage_menu(): void
    {
        $kasir = User::factory()
            ->kasir()
            ->create();

        $response = $this
            ->actingAs($kasir)
            ->getJson('/manajer/menu');

        $response->assertForbidden();
    }

    /**
     * Test 9: Menu yang mempunyai resep tidak bisa dihapus.
     */
    public function test_menu_with_recipe_cannot_be_deleted(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $menu = $this->createMenu();

        $bahan = BahanBaku::create([

            'nama' => 'Biji Kopi Arabika',

            'nama_normalisasi' => 'biji kopi arabika',

            'satuan_dasar' => 'g',

            'jumlah_stok' => 1000,

            'stok_minimum' => 0,

            'harga_per_satuan_dasar' => 180,

        ]);

        DetailResep::create([

            'menu_id' => $menu->id,

            'bahan_baku_id' => $bahan->id,

            'jumlah_per_porsi' => 18,

        ]);

        $response = $this
            ->actingAs($manager)
            ->deleteJson(
                '/manajer/menu/' . $menu->id
            );

        $response->assertUnprocessable();

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
        ]);
    }

    /**
     * Test 10: Menu yang belum mempunyai relasi bisnis bisa dihapus.
     */
    public function test_unused_menu_can_be_deleted(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $menu = $this->createMenu();

        $response = $this
            ->actingAs($manager)
            ->deleteJson(
                '/manajer/menu/' . $menu->id
            );

        $response->assertOk();

        $this->assertDatabaseMissing('menus', [
            'id' => $menu->id,
        ]);
    }
}