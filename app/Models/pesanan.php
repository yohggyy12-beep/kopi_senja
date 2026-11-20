<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesanan extends Model
{
    protected $table = 'pesanan';

    public $fillable = [
        'nomor_pesanan',
        'dibuat_oleh_pengguna_id',
        'nomor_meja',
        'metode_pembayaran',
        'status',
        'subtotal',
        'biaya_layanan',
        'jumlah_pajak',
        'total_harga',
    ];

    public function casts(): array
    {
        return [
            'subtotal' => 'float',
            'biaya_layanan' => 'float',
            'jumlah_pajak' => 'float',
            'total_harga' => 'float',
        ];
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh_pengguna_id');
    }

    public function dibuatOlehPengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh_pengguna_id');
    }

    public function detailPesanan(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id');
    }
}

