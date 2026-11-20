<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\PembelianStok;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PembelianStokService
{
    public function catat(
        array $data,
        User $pengguna
    ): PembelianStok {

        return DB::transaction(function () use ($data, $pengguna) {

            // Ambil bahan baku dan kunci baris selama transaksi
            $bahanBaku = BahanBaku::query()
                ->lockForUpdate()
                ->findOrFail($data['bahan_baku_id']);

            // Simpan catatan pembelian
            $pembelian = PembelianStok::create([
                'bahan_baku_id' => $bahanBaku->id,
                'dicatat_oleh_pengguna_id' => $pengguna->id,
                'jumlah' => $data['jumlah'],
                'tanggal_pembelian' => $data['tanggal_pembelian'],
            ]);

            // Tambahkan jumlah stok bahan baku
            $bahanBaku->increment(
                'jumlah_stok',
                (float) $data['jumlah']
            );

            // Kembalikan data pembelian beserta relasinya
            return $pembelian->load([
                'bahan_baku',
                'dicatat_oleh_pengguna',
            ]);
        });
    }
}
