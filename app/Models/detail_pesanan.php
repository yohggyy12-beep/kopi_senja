<?php   // OrderItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class detail_pesanan extends Model
{
    protected $fillable = [
        'pesanan_id', 'menu_id', 'nama_menu_saat_pemesanan', 'jumlah',
        'harga_satuan_saat_pemesanan', 'subtotal',
    ];

    protected function casts(): array
    {
        return ['harga_satuan_saat_pemesanan' => 'float', 'subtotal' => 'float'];
    }

    public function pesanan()           { return $this->belongsTo(pesanan::class); }
    public function menu()              { return $this->belongsTo(menu::class); }
    public function DetailBahanPesanan()  { return $this->hasMany(detail_bahan_pesanan::class); }
}
