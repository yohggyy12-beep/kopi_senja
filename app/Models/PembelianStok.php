<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'jumlah' => 'decimal:3',
            'tanggal_pembelian' => 'date',
        ];
    }

    // Relasi ke bahan baku yang dibeli
    public function bahan_baku(): BelongsTo
    {
        return $this->belongsTo(
            BahanBaku::class,
            'bahan_baku_id'
        );
    }

    // Relasi ke pengguna yang mencatat pembelian
    public function dicatat_oleh_pengguna(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'dicatat_oleh_pengguna_id'
        );
    }
}
