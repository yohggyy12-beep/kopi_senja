<?php   // OrderItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DetailBahanPesanan;

class DetailPesanan extends Model
{
    protected $table = 'detail_pesanan';

    protected $fillable = [
        'pesanan_id', 'menu_id', 'nama_menu_saat_pemesanan', 'jumlah',
        'harga_satuan_saat_pemesanan', 'subtotal',
    ];

    protected function casts(): array
    {
        return ['harga_satuan_saat_pemesanan' => 'float', 'subtotal' => 'float'];
    }

    public function pesanan()           { return $this->belongsTo(Pesanan::class); }
    public function menu()              { return $this->belongsTo(Menu::class); }
    public function DetailBahanPesanan()  { return $this->hasMany(DetailBahanPesanan::class); }
}
