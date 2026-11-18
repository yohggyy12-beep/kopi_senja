<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BahanBaku extends Model
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
        static::saving(function (BahanBaku $bahan_baku) {
            $bahan_baku->nama_normalisasi = self::nama_normalisasi($bahan_baku->nama);
        });
    }
    public static function nama_normalisasi(string $nama): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $nama)));
    }

    public function detailResep(): HasMany
    {
        return $this->hasMany(DetailResep::class, 'bahan_baku_id');
    }

    public function pembelianStok(): HasMany
    {
        return $this->hasMany(PembelianStok::class, 'bahan_baku_id');
    }

    public function detailBahanPesanan(): HasMany
    {
        return $this->hasMany(DetailBahanPesanan::class, 'bahan_baku_id');
    }

    public function isStokRendah(): bool
    {
        return (float) $this->jumlah_stok
        <= (float) $this->stok_minimum;
    }
}
