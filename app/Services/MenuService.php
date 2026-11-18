<?php

namespace App\Services;

use App\Models\Menu;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MenuService
{
    /**
     * Menampilkan daftar menu dengan pencarian dan filter.
     */
    public function paginate(
        array $filters = []
    ): LengthAwarePaginator {

        $query = Menu::query()
            ->withCount([
                'detailResep',
                'detailPesanan',
            ]);

        // Pencarian berdasarkan nama menu.
        if (!empty($filters['q'])) {

            $keyword = trim($filters['q']);

            $query->where(
                'nama',
                'like',
                '%' . $keyword . '%'
            );
        }

        // Filter berdasarkan kategori.
        if (!empty($filters['kategori'])) {

            $query->where(
                'kategori',
                $filters['kategori']
            );
        }

        // Filter status menu.
        if (($filters['status'] ?? null) === 'aktif') {

            $query->where('status_aktif', true);

        } elseif (($filters['status'] ?? null) === 'nonaktif') {

            $query->where('status_aktif', false);

        }

        return $query
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * Menambahkan menu baru.
     */
    public function create(array $data): Menu
    {
        return DB::transaction(function () use ($data) {

            return Menu::create([

                'nama' => $data['nama'],

                'kategori' => $data['kategori'],

                'harga_jual' => $data['harga_jual'],

                // Menu baru aktif secara default.
                'status_aktif' => true,

                // Resep belum diisi pada tahap ini.
                'resep_lengkap' => false,

            ]);

        });
    }

    /**
     * Memperbarui informasi menu.
     */
    public function update(
        Menu $menu,
        array $data
    ): Menu {

        return DB::transaction(function () use (
            $menu,
            $data
        ) {

            // Batasi kolom yang boleh diperbarui.
            $changes = Arr::only($data, [
                'nama',
                'kategori',
                'harga_jual',
                'status_aktif',
            ]);

            $menu->update($changes);

            return $menu->refresh();

        });
    }

    /**
     * Menghapus menu yang belum mempunyai hubungan bisnis.
     */
    public function delete(Menu $menu): void
    {
        DB::transaction(function () use ($menu) {

            // Menu tidak boleh dihapus jika sudah memiliki resep.
            if ($menu->detailResep()->exists()) {

                throw ValidationException::withMessages([
                    'menu' =>
                        'Menu tidak dapat dihapus karena masih mempunyai resep.',
                ]);

            }

            // Menu tidak boleh dihapus jika sudah pernah dipesan.
            if ($menu->detailPesanan()->exists()) {

                throw ValidationException::withMessages([
                    'menu' =>
                        'Menu tidak dapat dihapus karena sudah memiliki riwayat pesanan.',
                ]);

            }

            $menu->delete();

        });
    }
}
