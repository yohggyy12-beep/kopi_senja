<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pesanan extends Model
{
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

    public function dibuat_oleh_pengguna(){ return $this->belongsTo(User::class, 'dibuat_oleh_pengguna_id');}
    public function DetailPesanan(){ return $this->hasMany(detail_pesanan::class);}
}
