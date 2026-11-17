<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DetailResep;

class Menu extends Model
{
    protected $fillable =
    [
    'nama',
    'kategori',
    'harga_jual',
    'status_aktif',
    'resep_lengkap'
     ];

    protected function casts(): array
    {
        return [
            'harga_jual' => 'float',
            'status_aktif' => 'boolean'];
    }

    public function detail_resep()
    {
        return $this->hasMany(DetailResep::class);
    }

    public function availability(): string
    {
        if (! $this->status_aktif) {
            return 'nonaktif';
        }
        if ($this->detail_resep->isEmpty()) {
            return 'belum_ada_resep';
        }
        foreach ($this->detail_resep as $detail) {
            if ($detail->bahan_baku->jumlah_stok < $detail->jumlah_per_porsi) {
                return 'habis';
            }
        }
        return 'tersedia';
    }

    public function hpp(): float
    {
        return round($this->detail_resep->sum(
            fn ($d) => $d->jumlah_per_porsi * $d->bahan_baku->harga_satuan_saat_pemesanan), 2);
    }

    public function margin(): float
    {
        return round($this->harga_jual - $this->hpp(), 2);
    }
}
