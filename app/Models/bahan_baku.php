<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class bahan_baku extends Model
{
    protected $table = 'bahan_baku';
    protected $guarded = ['id'];
    protected $fillable = [
        'nama',
        'nama_normalisasi',
        'kode_pemasok',
        'satuan_dasar',
        'jumlah_stok',
        'stok_minimum',
        'harga_per_satuan_dasar',
        'tanggal_pembaruan_harga_pemasok',
    ];


    protected function casts():array
    {
        return [
            'jumlah_stok' => 'float',
            'stok_minimum' => 'float',
            'harga_per_satuan_dasar' => 'float',
            'tanggal_pembaruan_harga_pemasok' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (bahan_baku $bahan_baku) {
            $bahan_baku->nama_normalisasi = self::nama_normalisasi($bahan_baku->nama);
        });
    }
    public static function nama_normalisasi(string $nama): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', '', $nama)));
    }

    public function islow_stock(): bool
    {
        return $this->jumlah_stok <= $this->stok_minimum;
    }
}
