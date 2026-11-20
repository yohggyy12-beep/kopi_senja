<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetailPesanan extends Model
{
    protected $table = 'detail_pesanan';

    protected $fillable = [
        'pesanan_id',
        'menu_id',
        'nama_menu_saat_pemesanan',
        'jumlah',
        'harga_satuan_saat_pemesanan',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'harga_satuan_saat_pemesanan' => 'float',
            'subtotal' => 'float',
        ];
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function detailBahanPesanan(): HasMany
    {
        return $this->hasMany(DetailBahanPesanan::class, 'detail_pesanan_id');
    }
}

