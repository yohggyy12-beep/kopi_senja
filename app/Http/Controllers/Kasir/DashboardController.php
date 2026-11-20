<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Pesanan;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $pesananHariIni = Pesanan::whereDate('created_at', $today)->get();
        $totalPesananShift = $pesananHariIni->count();
        $pesananSelesaiShift = $pesananHariIni->where('status', 'selesai')->count();
        $pesananAktifShift = $pesananHariIni->whereIn('status', ['baru', 'diproses'])->count();

        $pendapatanShift = $pesananHariIni->where('status', 'selesai')->sum('total_harga');

        $pesananDiproses = Pesanan::where('status', 'diproses')->count();

        $totalMenu = Menu::where('status_aktif', true)->count();
        $menuHabis = Menu::where('status_aktif', true)->where('resep_lengkap', false)->count();
        $menuTersedia = max(0, $totalMenu - $menuHabis);

        $antreanPesanan = Pesanan::with('detailPesanan')
            ->whereIn('status', ['baru', 'diproses'])
            ->orderByRaw("FIELD(status, 'baru', 'diproses')")
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        return view('kasir.dashboard', compact(
            'totalPesananShift',
            'pesananSelesaiShift',
            'pesananAktifShift',
            'pendapatanShift',
            'pesananDiproses',
            'totalMenu',
            'menuTersedia',
            'menuHabis',
            'antreanPesanan'
        ));
    }
}
