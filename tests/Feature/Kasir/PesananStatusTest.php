<?php

namespace Tests\Feature\Kasir;
use App\Models\BahanBaku;
use App\Models\DetailResep;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesananStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Membuat data pesanan untuk pengujian.
     * Stok awal susu 1.000 ml.
     * Resep memakai 150 ml per porsi.
     * Pesanan dibuat sebanyak 2 porsi.
     */
    private function buatDataPesanan(): array
    {
        $kasir = User::factory()->kasir()->create();

        $susu = BahanBaku::create([
            'nama' => 'Susu UHT',
            'nama_normalisasi' => 'susu uht',
            'kode_pemasok' => null,
            'satuan_dasar' => 'ml',
            'jumlah_stok' => 1000,
            'stok_minimum' => 0,
            'harga_per_satuan_dasar' => 18.54,
            'tanggal_pembaruan_harga_pemasok' => null,
        ]);

        $menu = Menu::create([
            'nama' => 'Latte Uji',
            'kategori' => 'Minuman',
            'harga_jual' => 25000,
            'status_aktif' => true,
            'resep_lengkap' => true,
        ]);

        DetailResep::create([
            'menu_id' => $menu->id,
            'bahan_baku_id' => $susu->id,
            'jumlah_per_porsi' => 150,
        ]);

        // Buat pesanan melalui endpoint Kasir.
        $response = $this
            ->actingAs($kasir)
            ->postJson(route('kasir.pesanan.store'), [
                'items' => [
                    [
                        'menu_id' => $menu->id,
                        'jumlah' => 2,
                    ],
                ],
                'metode_pembayaran' => 'tunai',
                'nomor_meja' => 1,
            ]);

        $response->assertCreated();

        $pesanan = Pesanan::firstOrFail();

        // Stok setelah checkout:
        // 1.000 ml - (150 ml x 2) = 700 ml.
        $this->assertEquals(
            700.0,
            (float) $susu->fresh()->jumlah_stok
        );

        return [
            'kasir' => $kasir,
            'pesanan' => $pesanan,
            'susu' => $susu,
        ];
    }

    /**
     * TEST 1:
     * Status Baru -> Diproses -> Selesai.
     */
    public function test_kasir_can_update_order_status_sequentially(): void
    {
        $data = $this->buatDataPesanan();

        $kasir = $data['kasir'];
        $pesanan = $data['pesanan'];

        $this->actingAs($kasir)
            ->putJson(
                route('kasir.pesanan.status', $pesanan->id),
                ['status' => 'diproses']
            )
            ->assertOk()
            ->assertJsonPath('data.status', 'diproses');

        $this->assertDatabaseHas('pesanan', [
            'id' => $pesanan->id,
            'status' => 'diproses',
        ]);

        $this->putJson(
            route('kasir.pesanan.status', $pesanan->id),
            ['status' => 'selesai']
        )
            ->assertOk()
            ->assertJsonPath('data.status', 'selesai');

        $this->assertDatabaseHas('pesanan', [
            'id' => $pesanan->id,
            'status' => 'selesai',
        ]);
    }

    /**
     * TEST 2:
     * Status Baru tidak boleh langsung menjadi Selesai.
     */
    public function test_order_cannot_skip_processing_status(): void
    {
        $data = $this->buatDataPesanan();

        $pesanan = $data['pesanan'];
        $susu = $data['susu'];

        $this->actingAs($data['kasir'])
            ->putJson(
                route('kasir.pesanan.status', $pesanan->id),
                ['status' => 'selesai']
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);

        $this->assertDatabaseHas('pesanan', [
            'id' => $pesanan->id,
            'status' => 'baru',
        ]);

        // Stok tidak berubah akibat perubahan status yang ditolak.
        $this->assertEquals(
            700.0,
            (float) $susu->fresh()->jumlah_stok
        );
    }

    /**
     * TEST 3:
     * Manajer tidak boleh mengubah status pesanan.
     */
    public function test_manager_cannot_update_order_status(): void
    {
        $data = $this->buatDataPesanan();

        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->putJson(
                route('kasir.pesanan.status', $data['pesanan']->id),
                ['status' => 'diproses']
            )
            ->assertForbidden();

        $this->assertDatabaseHas('pesanan', [
            'id' => $data['pesanan']->id,
            'status' => 'baru',
        ]);
    }

    /**
     * TEST 4:
     * Pembatalan pesanan Baru mengembalikan stok.
     * Pembatalan kedua kali tidak boleh menggandakan stok.
     */
    public function test_cancelling_new_order_restores_stock_only_once(): void
    {
        $data = $this->buatDataPesanan();

        $kasir = $data['kasir'];
        $pesanan = $data['pesanan'];
        $susu = $data['susu'];

        $this->actingAs($kasir)
            ->deleteJson(
                route('kasir.pesanan.destroy', $pesanan->id)
            )
            ->assertOk();

        $this->assertDatabaseHas('pesanan', [
            'id' => $pesanan->id,
            'status' => 'dibatalkan',
        ]);

        // Stok kembali menjadi 1.000 ml.
        $this->assertEquals(
            1000.0,
            (float) $susu->fresh()->jumlah_stok
        );

        // Coba batalkan lagi.
        $this->deleteJson(
            route('kasir.pesanan.destroy', $pesanan->id)
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['pesanan']);

        // Stok tidak boleh bertambah menjadi 1.300 ml.
        $this->assertEquals(
            1000.0,
            (float) $susu->fresh()->jumlah_stok
        );
    }

    /**
     * TEST 5:
     * Pesanan yang sudah Diproses tidak boleh dibatalkan.
     */
    public function test_processed_order_cannot_be_cancelled(): void
    {
        $data = $this->buatDataPesanan();

        $kasir = $data['kasir'];
        $pesanan = $data['pesanan'];
        $susu = $data['susu'];

        // Ubah status menjadi Diproses.
        $this->actingAs($kasir)
            ->putJson(
                route('kasir.pesanan.status', $pesanan->id),
                ['status' => 'diproses']
            )
            ->assertOk();

        // Coba batalkan pesanan yang sudah diproses.
        $this->deleteJson(
            route('kasir.pesanan.destroy', $pesanan->id)
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['pesanan']);

        // Status harus tetap Diproses.
        $this->assertDatabaseHas('pesanan', [
            'id' => $pesanan->id,
            'status' => 'diproses',
        ]);

        // Stok tidak boleh dikembalikan.
        $this->assertEquals(
            700.0,
            (float) $susu->fresh()->jumlah_stok
        );
    }
}
