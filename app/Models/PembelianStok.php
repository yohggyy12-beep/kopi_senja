<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BahanBaku;

class PembelianStok extends Model
{
    protected $table = 'pembelian_stok';

    public $fillable = [
        'bahan_baku_id',
        'dicatat_oleh_pengguna_id',
        'harga_satuan',
        'tanggal_pembelian',
    ];
    public function casts(): array
    {
        return [
            'jumlah' => 'float',
            'harga_satuan' => 'float',
            'tanggal_pembelian' => 'date',
        ];
    }
    public function bahan_baku(){ return $this->belongsTo(BahanBaku::class);}
    public function dicatat_oleh_pengguna(){ return $this->belongsTo(User::class, 'dicatat_oleh_pengguna_id');}

}
