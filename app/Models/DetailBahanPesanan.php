<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailBahanPesanan extends Model
{
    protected $table = 'detail_bahan_pesanan';

    protected $fillable = [
        'detail_pesanan_id',
        'bahan_baku_id',
        'jumlah_digunakan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_digunakan' => 'float',
        ];
    }

    public function detailPesanan(): BelongsTo
    {
        return $this->belongsTo(DetailPesanan::class, 'detail_pesanan_id');
    }

    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}

