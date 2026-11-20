<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\DetailBahanPesanan;
use App\Models\DetailPesanan;
use App\Models\Menu;
use App\Models\PembelianStok;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LaporanController extends Controller
{
    /**
     * Menampilkan analisis HPP dan Margin Kotor per menu.
     */
    public function hppMargin(): View
    {
        $menus = Menu::with(['detailResep.bahanBaku'])
            ->where('status_aktif', true)
            ->orderBy('nama')
            ->get();

        $daftarHpp = $menus->map(function ($menu) {
            $hpp = 0;
            $resepLengkap = $menu->resep_lengkap && $menu->detailResep->isNotEmpty();

            foreach ($menu->detailResep as $item) {
                $hargaSatuan = (float)($item->bahanBaku->harga_per_satuan_dasar ?? 0);
                $takaran = (float)$item->jumlah_per_porsi;
                $hpp += ($hargaSatuan * $takaran);
            }

            $hargaJual = (float)$menu->harga_jual;
            $marginKotor = max(0, $hargaJual - $hpp);
            $persentase = $hargaJual > 0 ? round(($marginKotor / $hargaJual) * 100, 1) : 0;

            return (object)[
                'id' => $menu->id,
                'nama' => $menu->nama,
                'kategori' => $menu->kategori,
                'harga_jual' => $hargaJual,
                'hpp' => $hpp,
                'margin_kotor' => $marginKotor,
                'persentase' => $persentase,
                'resep_lengkap' => $resepLengkap,
                'detail_resep' => $menu->detailResep,
            ];
        });

        return view('manajer.hpp-margin', compact('daftarHpp'));
    }

    /**
     * Menampilkan Laporan Kinerja Penjualan.
     */
    public function penjualan(Request $request): View
    {
        $mulai = $request->input('mulai', Carbon::now()->subDays(6)->toDateString());
        $selesai = $request->input('selesai', Carbon::now()->toDateString());

        $pesananQuery = Pesanan::query()
            ->whereIn('status', ['diproses', 'selesai'])
            ->whereDate('created_at', '>=', $mulai)
            ->whereDate('created_at', '<=', $selesai);

        $totalOmzet = (clone $pesananQuery)->sum('total_harga');
        $totalTransaksi = (clone $pesananQuery)->count();
        $rataRataStruk = $totalTransaksi > 0 ? round($totalOmzet / $totalTransaksi) : 0;

        // Penjualan per menu
        $penjualanMenu = DetailPesanan::query()
            ->join('pesanan', 'detail_pesanan.pesanan_id', '=', 'pesanan.id')
            ->whereIn('pesanan.status', ['diproses', 'selesai'])
            ->whereDate('pesanan.created_at', '>=', $mulai)
            ->whereDate('pesanan.created_at', '<=', $selesai)
            ->select(
                'detail_pesanan.nama_menu_saat_pemesanan as nama',
                DB::raw('SUM(detail_pesanan.jumlah) as total_terjual'),
                DB::raw('SUM(detail_pesanan.subtotal) as total_pendapatan')
            )
            ->groupBy('detail_pesanan.nama_menu_saat_pemesanan')
            ->orderByDesc('total_pendapatan')
            ->get();

        return view('manajer.laporan-penjualan', compact(
            'mulai',
            'selesai',
            'totalOmzet',
            'totalTransaksi',
            'rataRataStruk',
            'penjualanMenu'
        ));
    }

    /**
     * Menampilkan Laporan Arus Persediaan Bahan Baku.
     */
    public function persediaan(Request $request): View
    {
        $mulai = $request->input('mulai', Carbon::now()->subDays(6)->toDateString());
        $selesai = $request->input('selesai', Carbon::now()->toDateString());

        $bahanBaku = BahanBaku::orderBy('nama')->get();

        $daftarPersediaan = $bahanBaku->map(function ($bahan) use ($mulai, $selesai) {
            // Jumlah pembelian masuk dalam periode
            $masuk = PembelianStok::where('bahan_baku_id', $bahan->id)
                ->whereDate('tanggal_pembelian', '>=', $mulai)
                ->whereDate('tanggal_pembelian', '<=', $selesai)
                ->sum('jumlah');

            // Jumlah pemakaian bahan dalam periode
            $terpakai = DetailBahanPesanan::where('bahan_baku_id', $bahan->id)
                ->whereHas('detailPesanan.pesanan', function ($q) use ($mulai, $selesai) {
                    $q->whereIn('status', ['diproses', 'selesai'])
                      ->whereDate('created_at', '>=', $mulai)
                      ->whereDate('created_at', '<=', $selesai);
                })
                ->sum('jumlah_digunakan');

            $sisaAkhir = (float)$bahan->jumlah_stok;
            $nilaiStok = $sisaAkhir * (float)($bahan->harga_per_satuan_dasar ?? 0);

            return (object)[
                'nama' => $bahan->nama,
                'satuan' => $bahan->satuan_dasar,
                'masuk' => $masuk,
                'terpakai' => $terpakai,
                'sisa_akhir' => $sisaAkhir,
                'nilai_stok' => $nilaiStok,
            ];
        });

        return view('manajer.laporan-persediaan', compact('mulai', 'selesai', 'daftarPersediaan'));
    }
}
