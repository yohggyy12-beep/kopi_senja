<?php

namespace Tests\Feature\Manajer;

use App\Models\User;
use App\Models\Menu;
use App\Models\BahanBaku;
use App\Models\DetailResep;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

use Tests\TestCase;

class ResepManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Membuat menu untuk testing.
     */
    private function createMenu(): Menu
    {
        return Menu::create([

            'nama' => 'Latte',

            'kategori' => 'Minuman',

            'harga_jual' => 25000,

            'status_aktif' => true,

            'resep_lengkap' => false,

        ]);
    }

    /**
     * Membuat bahan untuk testing.
     */
    private function createBahan(
        string $nama,
        string $satuan
    ): BahanBaku {

        return BahanBaku::create([

            'nama' => $nama,

            'nama_normalisasi' =>
                Str::lower(trim($nama)),

            'kode_pemasok' => null,

            'satuan_dasar' => $satuan,

            'jumlah_stok' => 1000,

            'stok_minimum' => 0,

            'harga_per_satuan_dasar' => 100,

        ]);
    }

    /**
     * Test 1: Manajer berhasil membuat resep.
     */
    public function test_manager_can_create_recipe(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $menu = $this->createMenu();

        $kopi = $this->createBahan(
            'Biji Kopi Arabika',
            'g'
        );

        $susu = $this->createBahan(
            'Susu UHT',
            'ml'
        );

        $response = $this
            ->actingAs($manager)
            ->postJson(
                '/manajer/menu/' . $menu->id . '/resep',
                [

                    'bahan' => [

                        [
                            'bahan_baku_id' => $kopi->id,
                            'jumlah_per_porsi' => 18,
                        ],

                        [
                            'bahan_baku_id' => $susu->id,
                            'jumlah_per_porsi' => 150,
                        ],

                    ],

                ]
            );

        $response->assertCreated();

        $this->assertDatabaseHas('detail_resep', [

            'menu_id' => $menu->id,

            'bahan_baku_id' => $kopi->id,

            'jumlah_per_porsi' => 18,

        ]);

        $this->assertDatabaseHas('detail_resep', [

            'menu_id' => $menu->id,

            'bahan_baku_id' => $susu->id,

            'jumlah_per_porsi' => 150,

        ]);

        $this->assertDatabaseHas('menus', [

            'id' => $menu->id,

            'resep_lengkap' => true,

        ]);
    }

    /**
     * Test 2: Resep harus memiliki minimal satu bahan.
     */
    public function test_recipe_requires_at_least_one_ingredient(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $menu = $this->createMenu();

        $response = $this
            ->actingAs($manager)
            ->postJson(
                '/manajer/menu/' . $menu->id . '/resep',
                [
                    'bahan' => [],
                ]
            );

        $response->assertUnprocessable();

        $response->assertJsonValidationErrors([
            'bahan',
        ]);
    }

    /**
     * Test 3: Bahan yang sama tidak boleh dimasukkan dua kali.
     */
    public function test_duplicate_ingredient_is_rejected(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $menu = $this->createMenu();

        $kopi = $this->createBahan(
            'Biji Kopi Arabika',
            'g'
        );

        $response = $this
            ->actingAs($manager)
            ->postJson(
                '/manajer/menu/' . $menu->id . '/resep',
                [

                    'bahan' => [

                        [
                            'bahan_baku_id' => $kopi->id,
                            'jumlah_per_porsi' => 18,
                        ],

                        [
                            'bahan_baku_id' => $kopi->id,
                            'jumlah_per_porsi' => 20,
                        ],

                    ],

                ]
            );

        $response->assertUnprocessable();

        $response->assertJsonValidationErrors([
            'bahan.1.bahan_baku_id',
        ]);
    }

    /**
     * Test 4: Takaran nol dan negatif ditolak.
     */
    public function test_invalid_recipe_quantity_is_rejected(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $menu = $this->createMenu();

        $kopi = $this->createBahan(
            'Biji Kopi Arabika',
            'g'
        );

        $response = $this
            ->actingAs($manager)
            ->postJson(
                '/manajer/menu/' . $menu->id . '/resep',
                [

                    'bahan' => [

                        [
                            'bahan_baku_id' => $kopi->id,
                            'jumlah_per_porsi' => 0,
                        ],

                    ],

                ]
            );

        $response->assertUnprocessable();

        $response->assertJsonValidationErrors([
            'bahan.0.jumlah_per_porsi',
        ]);
    }

    /**
     * Test 5: Manajer dapat memperbarui komposisi resep.
     */
    public function test_manager_can_update_recipe(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $menu = $this->createMenu();

        $kopi = $this->createBahan(
            'Biji Kopi Arabika',
            'g'
        );

        $susu = $this->createBahan(
            'Susu UHT',
            'ml'
        );

        $gelas = $this->createBahan(
            'Gelas',
            'pcs'
        );

        // Membuat resep awal.
        DetailResep::create([

            'menu_id' => $menu->id,

            'bahan_baku_id' => $kopi->id,

            'jumlah_per_porsi' => 18,

        ]);

        DetailResep::create([

            'menu_id' => $menu->id,

            'bahan_baku_id' => $susu->id,

            'jumlah_per_porsi' => 150,

        ]);

        $menu->update([
            'resep_lengkap' => true,
        ]);

        // Memperbarui seluruh komposisi.
        $response = $this
            ->actingAs($manager)
            ->putJson(
                '/manajer/menu/' . $menu->id . '/resep',
                [

                    'bahan' => [

                        [
                            'bahan_baku_id' => $kopi->id,
                            'jumlah_per_porsi' => 20,
                        ],

                        [
                            'bahan_baku_id' => $gelas->id,
                            'jumlah_per_porsi' => 1,
                        ],

                    ],

                ]
            );

        $response->assertOk();

        // Kopi diperbarui menjadi 20 gram.
        $this->assertDatabaseHas('detail_resep', [

            'menu_id' => $menu->id,

            'bahan_baku_id' => $kopi->id,

            'jumlah_per_porsi' => 20,

        ]);

        // Susu dilepaskan dari resep.
        $this->assertDatabaseMissing('detail_resep', [

            'menu_id' => $menu->id,

            'bahan_baku_id' => $susu->id,

        ]);

        // Gelas ditambahkan ke resep.
        $this->assertDatabaseHas('detail_resep', [

            'menu_id' => $menu->id,

            'bahan_baku_id' => $gelas->id,

            'jumlah_per_porsi' => 1,

        ]);
    }

    /**
     * Test 6: Kasir tidak boleh mengelola resep.
     */
    public function test_kasir_cannot_manage_recipe(): void
    {
        $kasir = User::factory()
            ->kasir()
            ->create();

        $menu = $this->createMenu();

        $response = $this
            ->actingAs($kasir)
            ->getJson(
                '/manajer/menu/' . $menu->id . '/resep'
            );

        $response->assertForbidden();
    }

    /**
     * Test 7: Resep dapat dihapus seluruhnya.
     */
    public function test_manager_can_delete_recipe(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $menu = $this->createMenu();

        $kopi = $this->createBahan(
            'Biji Kopi Arabika',
            'g'
        );

        DetailResep::create([

            'menu_id' => $menu->id,

            'bahan_baku_id' => $kopi->id,

            'jumlah_per_porsi' => 18,

        ]);

        $menu->update([
            'resep_lengkap' => true,
        ]);

        $response = $this
            ->actingAs($manager)
            ->deleteJson(
                '/manajer/menu/' . $menu->id . '/resep'
            );

        $response->assertOk();

        $this->assertDatabaseMissing('detail_resep', [

            'menu_id' => $menu->id,

            'bahan_baku_id' => $kopi->id,

        ]);

        $this->assertDatabaseHas('menus', [

            'id' => $menu->id,

            'resep_lengkap' => false,

        ]);
    }

    /**
     * Test 8: Membuat resep tidak mengubah stok bahan.
     */
    public function test_creating_recipe_does_not_reduce_stock(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $menu = $this->createMenu();

        $kopi = $this->createBahan(
            'Biji Kopi Arabika',
            'g'
        );

        $stokAwal = $kopi->jumlah_stok;

        $this
            ->actingAs($manager)
            ->postJson(
                '/manajer/menu/' . $menu->id . '/resep',
                [

                    'bahan' => [

                        [
                            'bahan_baku_id' => $kopi->id,
                            'jumlah_per_porsi' => 18,
                        ],

                    ],

                ]
            )
            ->assertCreated();

        $this->assertEquals(
            $stokAwal,
            $kopi->fresh()->jumlah_stok
        );
    }
}
