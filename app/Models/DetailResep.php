<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailResep extends Model
{
    protected $table = 'detail_resep';

    protected $fillable = [
        'menu_id',
        'bahan_baku_id',
        'jumlah_per_porsi',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_per_porsi' => 'decimal:3',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(
            Menu::class,
            'menu_id'
        );
    }

    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(
            BahanBaku::class,
            'bahan_baku_id'
        );
    }
}
