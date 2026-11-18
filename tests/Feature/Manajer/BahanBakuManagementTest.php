<?php

namespace Tests\Feature\Manajer;

use App\Models\User;
use App\Models\BahanBaku;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

use Tests\TestCase;

class BahanBakuManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createBahan(
        array $override = []
    ): BahanBaku {

        $data = array_merge([

            'nama' => 'Biji Kopi Arabika',
            'satuan_dasar' => 'g',
            'jumlah_stok' => 0,
            'stok_minimum' => 0,
            'kode_pemasok' => null,
            'harga_per_satuan_dasar' => 0,

        ], $override);

        $data['nama_normalisasi'] =
            Str::lower(trim($data['nama']));

        return BahanBaku::create($data);
    }

    /**
     * Test 1: Manajer berhasil menambah bahan.
     */
    public function test_manager_can_create_ingredient(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $response = $this
            ->actingAs($manager)
            ->postJson('/manajer/bahan-baku', [
                'nama' => 'Susu UHT',
                'satuan_dasar' => 'ml',
                'stok_minimum' => 500,

            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('bahan_baku', [
            'nama' => 'Susu UHT',
            'nama_normalisasi' => 'susu uht',
            'satuan_dasar' => 'ml',
            'jumlah_stok' => 0,
            'stok_minimum' => 500,

        ]);
    }

    public function test_duplicate_normalized_name_is_rejected(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();
        $this->createBahan([
            'nama' => 'Biji Kopi Arabika',
        ]);

        $response = $this
            ->actingAs($manager)
            ->postJson('/manajer/bahan-baku', [
                'nama' => '  BIJI KOPI ARABIKA  ',
                'satuan_dasar' => 'g',
                'stok_minimum' => 100,
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'nama_normalisasi',
        ]);
    }
    public function test_invalid_unit_is_rejected(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $response = $this
            ->actingAs($manager)
            ->postJson('/manajer/bahan-baku', [
                'nama' => 'Susu UHT',
                'satuan_dasar' => 'liter',
                'stok_minimum' => 500,

            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'satuan_dasar',
        ]);
    }
    public function test_kasir_cannot_manage_ingredients(): void
    {
        $kasir = User::factory()
            ->kasir()
            ->create();

        $response = $this
            ->actingAs($kasir)
            ->getJson('/manajer/bahan-baku');

        $response->assertForbidden();
    }

    public function test_unit_cannot_change_when_ingredient_has_stock(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $bahan = $this->createBahan([
            'jumlah_stok' => 1000,
        ]);

        $response = $this
            ->actingAs($manager)
            ->putJson(
                '/manajer/bahan-baku/' . $bahan->id,
                [
                    'satuan_dasar' => 'ml',
                ]
            );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'satuan_dasar',
        ]);
    }

    public function test_unused_ingredient_can_be_deleted(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $bahan = $this->createBahan();
        $response = $this
            ->actingAs($manager)
            ->deleteJson(
                '/manajer/bahan-baku/' . $bahan->id
            );

        $response->assertOk();
        $this->assertDatabaseMissing('bahan_baku', [
            'id' => $bahan->id,
        ]);
    }

    public function test_ingredient_with_stock_cannot_be_deleted(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $bahan = $this->createBahan([
            'jumlah_stok' => 500,
        ]);

        $response = $this
            ->actingAs($manager)
            ->deleteJson(
                '/manajer/bahan-baku/' . $bahan->id
            );

        $response->assertUnprocessable();

        $this->assertDatabaseHas('bahan_baku', [
            'id' => $bahan->id,
        ]);
    }

    public function test_stock_cannot_be_changed_through_ingredient_update(): void
    {
        $manager = User::factory()
            ->manager()
            ->create();

        $bahan = $this->createBahan([
            'jumlah_stok' => 200,
        ]);

        $response = $this
            ->actingAs($manager)
            ->putJson(
                '/manajer/bahan-baku/' . $bahan->id,
                [
                    'stok_minimum' => 50,
                    'jumlah_stok' => 999999,
                ]
            );

        $response->assertOk();

        $this->assertDatabaseHas('bahan_baku', [
            'id' => $bahan->id,
            'jumlah_stok' => 200,
            'stok_minimum' => 50,
        ]);
    }
}