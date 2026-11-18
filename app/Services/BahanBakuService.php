<?php

namespace App\Services;

use App\Models\BahanBaku;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BahanBakuService
{
    /**
     * Menampilkan daftar bahan baku.
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = BahanBaku::query();

        // Pencarian berdasarkan nama.
        if (!empty($filters['q'])) {

            $keyword = trim($filters['q']);

            $query->where(
                'nama_normalisasi',
                'like',
                '%' . mb_strtolower($keyword) . '%'
            );
        }

        // Filter bahan yang stoknya menipis.
        if (($filters['status'] ?? null) === 'menipis') {

            $query->whereColumn(
                'jumlah_stok',
                '<=',
                'stok_minimum'
            );
        }

        // Urutkan berdasarkan nama.
        return $query
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * Membuat bahan baku baru.
     */
    public function create(array $data): BahanBaku
    {
        return DB::transaction(function () use ($data) {

            return BahanBaku::create([

                'nama' => $data['nama'],

                'nama_normalisasi' =>
                    $data['nama_normalisasi'],

                'satuan_dasar' =>
                    $data['satuan_dasar'],

                // Bahan baru dimulai dengan stok kosong.
                'jumlah_stok' => 0,

                'stok_minimum' =>
                    $data['stok_minimum'],

                'kode_pemasok' =>
                    $data['kode_pemasok'] ?? null,

                // Harga awal menunggu pembaruan Supplier.
                'harga_per_satuan_dasar' => 0,

            ]);

        });
    }

    /**
     * Memperbarui data bahan baku.
     */
    public function update(
        BahanBaku $bahanBaku,
        array $data
    ): BahanBaku {

        return DB::transaction(function () use (
            $bahanBaku,
            $data
        ) {

            // Periksa apakah satuan akan diubah.
            if (
                isset($data['satuan_dasar']) &&
                $data['satuan_dasar'] !== $bahanBaku->satuan_dasar
            ) {

                if ($this->hasHistory($bahanBaku)) {

                    throw ValidationException::withMessages([
                        'satuan_dasar' =>
                            'Satuan tidak dapat diubah karena bahan sudah digunakan.',
                    ]);

                }
            }

            // Batasi kolom yang boleh diperbarui.
            $changes = Arr::only($data, [
                'nama',
                'nama_normalisasi',
                'satuan_dasar',
                'stok_minimum',
                'kode_pemasok',
            ]);

            $bahanBaku->update($changes);

            return $bahanBaku->refresh();

        });
    }

    /**
     * Menghapus bahan baku jika belum mempunyai riwayat.
     */
    public function delete(BahanBaku $bahanBaku): void
    {
        DB::transaction(function () use ($bahanBaku) {

            if ($this->hasHistory($bahanBaku)) {

                throw ValidationException::withMessages([
                    'bahan_baku' =>
                        'Bahan tidak dapat dihapus karena masih memiliki stok atau riwayat penggunaan.',
                ]);

            }

            $bahanBaku->delete();

        });
    }

    /**
     * Memeriksa apakah bahan memiliki riwayat.
     */
    private function hasHistory(BahanBaku $bahanBaku): bool
    {
        return
            (float) $bahanBaku->jumlah_stok != 0 ||

            $bahanBaku->detailResep()->exists() ||

            $bahanBaku->pembelianStok()->exists() ||

            $bahanBaku->detailBahanPesanan()->exists();
    }
}
