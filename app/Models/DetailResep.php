<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailResep extends Model
{
    protected $table = 'detail_resep';

    protected $fillable = [
        'menu_id',
        'bahan_baku_id',
        'jumlah_per_porsi',
    ];

    public function casts(): array
    {
        return [
            'jumlah_per_porsi' => 'float',
        ];
    }
    public function menu(){ return $this->belongsTo(Menu::class);}
    public function bahan_baku(){ return $this->belongsTo(BahanBaku::class);}
}
