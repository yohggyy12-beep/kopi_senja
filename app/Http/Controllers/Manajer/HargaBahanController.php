<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use App\Services\SupplierHargaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HargaBahanController extends Controller
{
    /**
     * Menampilkan daftar harga bahan dari Supplier.
     */
    public function index(Request $request): JsonResponse|View
    {
        $daftarBahan = BahanBaku::query()
            ->orderBy('nama')
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Daftar harga bahan baku supplier.',
                'data' => $daftarBahan,
            ]);
        }

        $terakhirSinkron = BahanBaku::whereNotNull('tanggal_pembaruan_harga_pemasok')
            ->max('tanggal_pembaruan_harga_pemasok');

        return view('manajer.harga-bahan', compact('daftarBahan', 'terakhirSinkron'));
    }

    /**
     * Memperbarui harga dari API Supplier eksternal.
     */
    public function perbarui(
        SupplierHargaService $service,
        Request $request
    ): JsonResponse|RedirectResponse {
        $hasil = $service->perbaruiHarga();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $hasil['pesan'],
                'data' => $hasil,
            ]);
        }

        if ($hasil['berhasil']) {
            return redirect()->route('manajer.harga-bahan.index')
                ->with('success', $hasil['pesan'])
                ->with('sync_result', $hasil);
        }

        return redirect()->route('manajer.harga-bahan.index')
            ->with('warning', $hasil['pesan'])
            ->with('sync_result', $hasil);
    }
}
