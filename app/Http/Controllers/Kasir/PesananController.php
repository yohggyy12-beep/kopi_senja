<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePesananRequest;
use App\Http\Requests\UpdateStatusPesananRequest;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Services\PesananService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PesananController extends Controller
{
    /**
     * Menampilkan daftar pesanan.
     */
    public function index(Request $request): JsonResponse|View
    {
        $query = Pesanan::query()
            ->with(['dibuatOlehPengguna', 'detailPesanan'])
            ->orderByDesc('created_at');

        $statusAktif = $request->input('status', 'semua');
        if ($statusAktif !== 'semua' && in_array($statusAktif, ['baru', 'diproses', 'selesai', 'dibatalkan'])) {
            $query->where('status', $statusAktif);
        }

        $q = $request->input('q');
        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nomor_pesanan', 'like', "%{$q}%")
                    ->orWhere('nomor_meja', 'like', "%{$q}%");
            });
        }

        $pesanan = $query->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Daftar pesanan berhasil diambil.',
                'data' => $pesanan,
            ]);
        }

        return view('kasir.pesanan.index', compact('pesanan', 'statusAktif', 'q'));
    }

    /**
     * Menampilkan form POS untuk membuat pesanan baru.
     */
    public function create(): View
    {
        $menus = Menu::query()
            ->where('status_aktif', true)
            ->with(['detailResep.bahanBaku'])
            ->orderBy('nama')
            ->get()
            ->map(function ($menu) {
                // Cek apakah semua bahan resep mencukupi untuk minimal 1 porsi
                $bisaDibuat = $menu->resep_lengkap && $menu->detailResep->isNotEmpty();
                if ($bisaDibuat) {
                    foreach ($menu->detailResep as $itemResep) {
                        $bahan = $itemResep->bahanBaku;
                        if (!$bahan || (float)$bahan->jumlah_stok < (float)$itemResep->jumlah_per_porsi) {
                            $bisaDibuat = false;
                            break;
                        }
                    }
                }
                $menu->is_tersedia = $bisaDibuat;
                return $menu;
            });

        $kategoriList = $menus->pluck('kategori')->filter()->unique()->values();

        return view('kasir.pesanan.create', compact('menus', 'kategoriList'));
    }

    /**
     * Menyimpan pesanan baru.
     */
    public function store(
        StorePesananRequest $request,
        PesananService $service
    ): JsonResponse|RedirectResponse {
        $pesanan = $service->buatPesanan(
            $request->validated(),
            $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Pesanan berhasil dibuat.',
                'data' => $pesanan,
            ], 201);
        }

        return redirect()->route('kasir.pesanan.show', $pesanan->id)
            ->with('success', 'Pesanan #' . $pesanan->nomor_pesanan . ' berhasil dibuat.');
    }

    /**
     * Menampilkan detail pesanan.
     */
    public function show(Pesanan $pesanan, Request $request): JsonResponse|View
    {
        $pesanan->load([
            'dibuatOlehPengguna',
            'detailPesanan.menu',
            'detailPesanan.detailBahanPesanan.bahanBaku',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Detail pesanan berhasil diambil.',
                'data' => $pesanan,
            ]);
        }

        return view('kasir.pesanan.show', compact('pesanan'));
    }

    /**
     * Menampilkan struk pesanan siap cetak.
     */
    public function struk(Pesanan $pesanan): View
    {
        $pesanan->load([
            'dibuatOlehPengguna',
            'detailPesanan',
        ]);

        return view('kasir.pesanan.struk', compact('pesanan'));
    }

    /**
     * Mengubah status pesanan.
     */
    public function updateStatus(
        UpdateStatusPesananRequest $request,
        Pesanan $pesanan,
        PesananService $service
    ): JsonResponse|RedirectResponse {
        $statusBaru = $request->validated('status');
        $hasil = $service->ubahStatus(
            $pesanan,
            $statusBaru
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Status pesanan berhasil diperbarui.',
                'data' => $hasil,
            ]);
        }

        return redirect()->back()
            ->with('success', 'Status pesanan #' . $pesanan->nomor_pesanan . ' diperbarui menjadi: ' . ucfirst($statusBaru));
    }

    /**
     * Membatalkan pesanan.
     */
    public function destroy(
        Pesanan $pesanan,
        PesananService $service,
        Request $request
    ): JsonResponse|RedirectResponse {
        $hasil = $service->batalkanPesanan($pesanan);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Pesanan berhasil dibatalkan dan stok dikembalikan.',
                'data' => $hasil,
            ]);
        }

        return redirect()->route('kasir.pesanan.index')
            ->with('success', 'Pesanan #' . $pesanan->nomor_pesanan . ' berhasil dibatalkan dan seluruh stok bahan telah dikembalikan.');
    }
}
