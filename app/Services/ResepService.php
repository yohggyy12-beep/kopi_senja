<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\DetailResep;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ResepService
{
    /**
     * Menampilkan daftar menu beserta resepnya dengan pencarian dan filter.
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Menu::query()
            ->with(['detailResep.bahan_baku']);

        if (!empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where('nama', 'like', '%' . $keyword . '%');
        }

        if (isset($filters['resep_lengkap'])) {
            $query->where(
                'resep_lengkap',
                filter_var($filters['resep_lengkap'], FILTER_VALIDATE_BOOLEAN)
            );
        }

        return $query
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * Mengambil data resep untuk menu tertentu.
     */
    public function getByMenu(Menu $menu): Menu
    {
        return $menu->load(['detailResep.bahan_baku']);
    }

    /**
     * Menyimpan atau memperbarui daftar resep untuk menu.
     */
    public function save(Menu $menu, array $items): Menu
    {
        return DB::transaction(function () use ($menu, $items) {
            // Hapus resep yang lama untuk menu ini
            $menu->detailResep()->delete();

            // Simpan resep yang baru
            foreach ($items as $item) {
                $menu->detailResep()->create([
                    'bahan_baku_id' => $item['bahan_baku_id'],
                    'jumlah_per_porsi' => $item['jumlah_per_porsi'],
                ]);
            }

            // Update status resep lengkap pada menu
            $menu->update([
                'resep_lengkap' => count($items) > 0,
            ]);

            return $menu->fresh(['detailResep.bahan_baku']);
        });
    }

    /**
     * Menghapus semua resep pada suatu menu.
     */
    public function delete(Menu $menu): void
    {
        DB::transaction(function () use ($menu) {
            $menu->detailResep()->delete();

            $menu->update([
                'resep_lengkap' => false,
            ]);
        });
    }
}
