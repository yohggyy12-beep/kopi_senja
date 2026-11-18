<?php   // OrderItemIngredient.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailBahanPesanan extends Model
{
    protected $table = 'detail_bahan_pesanan';

    protected $fillable = ['detail_pesanan_id', 'bahan_baku_id', 'jumlah_digunakan'];

    protected function casts(): array
    {
        return ['jumlah_digunakan' => 'float'];
    }
}
