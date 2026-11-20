<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\DetailPesanan;
use App\Models\Menu;
use App\Models\PembelianStok;
use App\Models\Pesanan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $jumlahMenu = Menu::count();
        $jumlahPesanan = Pesanan::count();
        $jumlahBahanBaku = BahanBaku::count();
        $jumlahStokMenipis = BahanBaku::query()
            ->whereColumn('jumlah_stok', '<=', 'stok_minimum')
            ->count();

        // Total penjualan dari pesanan yang tidak dibatalkan
        $totalPenjualan = Pesanan::whereIn('status', ['diproses', 'selesai'])->sum('total_harga');

        // Menu terlaris
        $menuTerlaris = DetailPesanan::query()
            ->select('nama_menu_saat_pemesanan as nama', DB::raw('SUM(jumlah) as jumlah_terjual'), 'harga_satuan_saat_pemesanan as harga_jual')
            ->groupBy('nama_menu_saat_pemesanan', 'harga_satuan_saat_pemesanan')
            ->orderByDesc('jumlah_terjual')
            ->limit(5)
            ->get();

        // Aktivitas terbaru: gabungan pesanan & pembelian stok
        $pesananTerbaru = Pesanan::with('detailPesanan')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($p) {
                $itemNames = $p->detailPesanan->map(fn($d) => "{$d->jumlah}x {$d->nama_menu_saat_pemesanan}")->take(2)->implode(', ');
                return [
                    'nomor' => '#' . $p->nomor_pesanan,
                    'keterangan' => $itemNames ?: 'Pesanan Meja ' . ($p->nomor_meja ?? '-'),
                    'nominal' => $p->total_harga,
                    'status' => $p->status,
                    'created_at' => $p->created_at,
                ];
            });

        $pembelianTerbaru = PembelianStok::with('bahan_baku')
            ->latest('tanggal_pembelian')
            ->limit(5)
            ->get()
            ->map(function ($b) {
                return [
                    'nomor' => '#STK-' . $b->id,
                    'keterangan' => 'Pembelian ' . ($b->bahan_baku->nama ?? 'Bahan') . ' ' . $b->jumlah . ' ' . ($b->bahan_baku->satuan_dasar ?? ''),
                    'nominal' => ($b->jumlah * ($b->bahan_baku->harga_per_satuan_dasar ?? 0)),
                    'status' => 'selesai',
                    'created_at' => $b->created_at ?? $b->tanggal_pembelian,
                ];
            });

        $aktivitasTerbaru = $pesananTerbaru->concat($pembelianTerbaru)
            ->sortByDesc('created_at')
            ->take(6)
            ->values();

        return view('manajer.dashboard', compact(
            'jumlahMenu',
            'jumlahPesanan',
            'jumlahBahanBaku',
            'jumlahStokMenipis',
            'totalPenjualan',
            'menuTerlaris',
            'aktivitasTerbaru'
        ));
    }
}
