<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembelianStok extends Model
{
    protected $table = 'pembelian_stok';

    protected $fillable = [
        'bahan_baku_id',
        'dicatat_oleh_pengguna_id',
        'jumlah',
        'tanggal_pembelian',
    ];

    protected function casts(): array
    {
        return [
            'jumlah'             => 'float',
            'tanggal_pembelian'  => 'date',
        ];
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function dicatatOleh()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh_pengguna_id');
    }
}
