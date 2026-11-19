<?php

namespace Tests\Feature\Manajer;

use App\Models\BahanBaku;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResepManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper untuk membuat menu pengujian.
     */
    private function createMenu(array $override = []): Menu
    {
        return Menu::create(array_merge([
            'nama' => 'Espresso',
            'kategori' => 'Minuman',
            'harga_jual' => 18000,
            'status_aktif' => true,
            'resep_lengkap' => false,
        ], $override));
    }

    /**
     * Helper untuk membuat bahan baku pengujian.
     */
    private function createBahan(array $override = []): BahanBaku
    {
        $data = array_merge([
            'nama' => 'Biji Kopi Arabika',
            'satuan_dasar' => 'g',
            'jumlah_stok' => 1000,
            'stok_minimum' => 100,
            'kode_pemasok' => null,
            'harga_per_satuan_dasar' => 150,
        ], $override);

        $data['nama_normalisasi'] = Str::lower(trim($data['nama']));

        return BahanBaku::create($data);
    }

    /**
     * Test 1: Manajer berhasil menyimpan resep untuk menu.
     */
    public function test_manager_can_save_recipe(): void
    {
        $manager = User::factory()->manager()->create();
        $menu = $this->createMenu();
        $bahan = $this->createBahan();

        $response = $this
            ->actingAs($manager)
            ->postJson('/manajer/resep', [
                'menu_id' => $menu->id,
                'items' => [
                    [
                        'bahan_baku_id' => $bahan->id,
                        'jumlah_per_porsi' => 18.5,
                    ],
                ],
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('detail_resep', [
            'menu_id' => $menu->id,
            'bahan_baku_id' => $bahan->id,
            'jumlah_per_porsi' => 18.5,
        ]);

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'resep_lengkap' => true,
        ]);
    }

    /**
     * Test 2: Manajer bisa melihat detail resep dari suatu menu.
     */
    public function test_manager_can_view_recipe(): void
    {
        $manager = User::factory()->manager()->create();
        $menu = $this->createMenu();

        $response = $this
            ->actingAs($manager)
            ->getJson('/manajer/resep/' . $menu->id);

        $response->assertOk();
    }

    /**
     * Test 3: Manajer bisa memperbarui resep menu.
     */
    public function test_manager_can_update_recipe(): void
    {
        $manager = User::factory()->manager()->create();
        $menu = $this->createMenu();
        $bahan = $this->createBahan();

        $response = $this
            ->actingAs($manager)
            ->putJson('/manajer/resep/' . $menu->id, [
                'items' => [
                    [
                        'bahan_baku_id' => $bahan->id,
                        'jumlah_per_porsi' => 20,
                    ],
                ],
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('detail_resep', [
            'menu_id' => $menu->id,
            'bahan_baku_id' => $bahan->id,
            'jumlah_per_porsi' => 20,
        ]);
    }

    /**
     * Test 4: Manajer bisa menghapus resep menu.
     */
    public function test_manager_can_delete_recipe(): void
    {
        $manager = User::factory()->manager()->create();
        $menu = $this->createMenu();
        $bahan = $this->createBahan();

        $menu->detailResep()->create([
            'bahan_baku_id' => $bahan->id,
            'jumlah_per_porsi' => 15,
        ]);
        $menu->update(['resep_lengkap' => true]);

        $response = $this
            ->actingAs($manager)
            ->deleteJson('/manajer/resep/' . $menu->id);

        $response->assertOk();

        $this->assertDatabaseMissing('detail_resep', [
            'menu_id' => $menu->id,
        ]);

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'resep_lengkap' => false,
        ]);
    }

    /**
     * Test 5: Kasir tidak boleh mengelola resep.
     */
    public function test_kasir_cannot_manage_recipe(): void
    {
        $kasir = User::factory()->kasir()->create();
        $menu = $this->createMenu();

        $response = $this
            ->actingAs($kasir)
            ->getJson('/manajer/resep/' . $menu->id);

        $response->assertForbidden();
    }
}
