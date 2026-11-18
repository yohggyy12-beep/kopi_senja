<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DetailResep;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Menu extends Model
{
    protected $table = 'menus';
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
            'harga_jual' => 'decimal:2',
            'status_aktif' => 'boolean',
            'resep_lengkap' => 'boolean',
            ];
    }

   public function detailResep(): HasMany
    {
        return $this->hasMany(
            DetailResep::class,
            'menu_id'
        );
    }

    public function bahanBakus(): BelongsToMany
    {
        return $this->belongsToMany(
            BahanBaku::class,
            'detail_resep',
            'menu_id',
            'bahan_baku_id'
        )
        ->withPivot('jumlah_per_porsi')
        ->withTimestamps();
    }

    public function detailPesanan(): HasMany
    {
        return $this->hasMany(
            DetailPesanan::class,
            'menu_id'
        );
    }

    /**
     * Memeriksa apakah menu sudah mempunyai resep.
     */
    public function hasResepLengkap(): bool
    {
        return $this->resep_lengkap
            && $this->detailResep()->exists();
    }

    /**
     * Memeriksa apakah menu bisa dipesan
     * berdasarkan status aktif dan resep.
     *
     * Pemeriksaan stok akan dilengkapi pada fase 04.
     */
    public function isBisaDipesan(): bool
    {
        return $this->status_aktif
            && $this->hasResepLengkap();
    }
}
