<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'nama',
        'nama_pengguna',
        'nomor_telepon',
        'email',
        'kata_sandi',
        'peran',
        'wajib_ganti_kata_sandi',
    ];
    protected $hidden = [
        'kata_sandi',
        'remember_token',
    ];
    protected function casts(): array
    {
        return [
            'kata_sandi' => 'hashed',
            'wajib_ganti_kata_sandi' => 'boolean',
        ];
    }
    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'menu_id');
    }

    // Memberi tahu Laravel lokasi password sebenarnya.
    public function getAuthPassword()
    {
        return $this->kata_sandi;
    }

    // Konstanta peran.
    public const PERAN_MANAGER = 'manager';
    public const PERAN_KASIR = 'kasir';
    public const PERAN_PELANGGAN = 'pelanggan';

    // Relasi dengan pesanan yang dibuat pengguna.
    public function pesananDibuat(): HasMany
    {
        return $this->hasMany(
            Pesanan::class,
            'dibuat_oleh_pengguna_id'
        );
    }

    // Relasi dengan pembelian stok yang dicatat pengguna.
    public function pembelianStokDicatat(): HasMany
    {
        return $this->hasMany(
            PembelianStok::class,
            'dicatat_oleh_pengguna_id'
        );
    }

    public function isManager(): bool
    {
        return $this->peran === self::PERAN_MANAGER;
    }

    public function isKasir(): bool
    {
        return $this->peran === self::PERAN_KASIR;
    }

    public function isPelanggan(): bool
    {
        return $this->peran === self::PERAN_PELANGGAN;
    }
}
