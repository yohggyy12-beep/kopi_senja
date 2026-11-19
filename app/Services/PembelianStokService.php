<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\PembelianStok;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PembelianStokService
{
    /**
     * Mencatat pembelian stok bahan baku.
     *
     * Dua hal dilakukan dalam satu transaksi:
     *   1. Simpan record ke tabel pembelian_stok.
     *   2. Tambahkan jumlah_stok pada bahan baku terkait.
     *
     * Jika salah satu langkah gagal, seluruh perubahan dibatalkan.
     */
    public function catat(array $data, User $pencatat): PembelianStok
    {
        $jumlah = (float) ($data['jumlah'] ?? 0);

        if ($jumlah <= 0) {
            throw ValidationException::withMessages([
                'jumlah' => 'Jumlah pembelian harus lebih dari 0.',
            ]);
        }

        return DB::transaction(function () use ($data, $pencatat, $jumlah) {

            // Ambil dan kunci baris bahan baku agar tidak ada perubahan paralel
            $bahan = BahanBaku::lockForUpdate()->findOrFail($data['bahan_baku_id']);

            // Simpan catatan pembelian
            $pembelian = PembelianStok::create([
                'bahan_baku_id'            => $bahan->id,
                'dicatat_oleh_pengguna_id' => $pencatat->id,
                'jumlah'                   => $jumlah,
                'tanggal_pembelian'        => $data['tanggal_pembelian'],
            ]);

            // Tambah stok bahan baku
            $bahan->increment('jumlah_stok', $jumlah);

            return $pembelian;
        });
    }

    /**
     * Menampilkan riwayat pembelian stok (opsional: filter berdasarkan bahan).
     */
    public function riwayat(?int $bahanBakuId = null)
    {
        return PembelianStok::with(['bahanBaku', 'dicatatOleh'])
            ->when($bahanBakuId, fn ($q) => $q->where('bahan_baku_id', $bahanBakuId))
            ->orderByDesc('tanggal_pembelian')
            ->orderByDesc('id')
            ->paginate(15);
    }
}
