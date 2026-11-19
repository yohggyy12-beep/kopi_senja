<?php

namespace App\Services;

use App\Models\Menu;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResepService
{
    /**
     * Menampilkan daftar menu beserta komposisi resepnya.
     */
    public function paginate(): LengthAwarePaginator
    {
        return Menu::query()
            ->with([
                'detailResep.bahanBaku',
            ])
            ->withCount('detailResep')
            ->orderBy('nama')
            ->paginate(15);
    }

    /**
     * Melihat resep dari satu menu.
     */
    public function show(Menu $menu): Menu
    {
        return $menu->load([
            'detailResep.bahanBaku',
        ]);
    }

    /**
     * Alias untuk melihat resep suatu menu.
     */
    public function getByMenu(Menu $menu): Menu
    {
        return $this->show($menu);
    }

    /**
     * Menyimpan / memperbarui resep untuk menu.
     */
    public function save(Menu $menu, array $items): Menu
    {
        return DB::transaction(function () use ($menu, $items) {
            $menu = Menu::query()
                ->lockForUpdate()
                ->findOrFail($menu->id);

            $syncData = $this->preparePivotData($items);

            $menu->bahanBakus()->sync($syncData);

            $menu->resep_lengkap = count($syncData) > 0;
            $menu->save();

            return $menu->fresh()->load([
                'detailResep.bahanBaku',
            ]);
        });
    }

    /**
     * Mengubah daftar bahan menjadi data pivot.
     */
    private function preparePivotData(array $items): array
    {
        $syncData = [];

        foreach ($items as $item) {

            $bahanId = (int) $item['bahan_baku_id'];

            $syncData[$bahanId] = [
                'jumlah_per_porsi' =>
                    $item['jumlah_per_porsi'],
            ];

        }

        return $syncData;
    }

    /**
     * Membuat resep baru.
     */
    public function create(
        Menu $menu,
        array $data
    ): Menu {

        return DB::transaction(function () use (
            $menu,
            $data
        ) {

            // Mengunci baris menu selama proses transaksi.
            $menu = Menu::query()
                ->lockForUpdate()
                ->findOrFail($menu->id);

            // Pastikan menu belum mempunyai resep.
            if ($menu->detailResep()->exists()) {

                throw ValidationException::withMessages([
                    'resep' =>
                        'Menu sudah memiliki resep. Gunakan fitur ubah resep.',
                ]);

            }

            // Siapkan data untuk tabel detail_resep.
            $syncData = $this->preparePivotData(
                $data['bahan']
            );

            // Simpan seluruh komposisi bahan.
            $menu->bahanBakus()->sync($syncData);

            // Tandai resep sudah tersedia.
            $menu->resep_lengkap = true;

            $menu->save();

            // Kembalikan menu beserta seluruh komposisinya.
            return $menu->fresh()->load([
                'detailResep.bahanBaku',
            ]);

        });

    }

    /**
     * Memperbarui seluruh komposisi resep.
     */
    public function update(
        Menu $menu,
        array $data
    ): Menu {

        return DB::transaction(function () use (
            $menu,
            $data
        ) {

            $menu = Menu::query()
                ->lockForUpdate()
                ->findOrFail($menu->id);

            // Pastikan resep sudah pernah dibuat.
            if (!$menu->detailResep()->exists()) {

                throw ValidationException::withMessages([
                    'resep' =>
                        'Menu belum memiliki resep. Gunakan fitur tambah resep.',
                ]);

            }

            $syncData = $this->preparePivotData(
                $data['bahan']
            );

            // Sinkronisasi komposisi baru.
            $menu->bahanBakus()->sync($syncData);

            // Resep tetap lengkap karena validasi minimal 1 bahan.
            $menu->resep_lengkap = true;

            $menu->save();

            return $menu->fresh()->load([
                'detailResep.bahanBaku',
            ]);

        });

    }

    /**
     * Menghapus seluruh komposisi resep suatu menu.
     */
    public function delete(Menu $menu): void
    {
        DB::transaction(function () use ($menu) {

            $menu = Menu::query()
                ->lockForUpdate()
                ->findOrFail($menu->id);

            if (!$menu->detailResep()->exists()) {

                throw ValidationException::withMessages([
                    'resep' =>
                        'Menu belum memiliki resep.',
                ]);

            }

            // Hapus hubungan bahan dengan menu.
            $menu->bahanBakus()->detach();

            // Menu tidak lagi mempunyai resep lengkap.
            $menu->resep_lengkap = false;

            $menu->save();

        });

    }
}
