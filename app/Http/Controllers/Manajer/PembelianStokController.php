<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePembelianStokRequest;
use App\Http\Requests\UpdateStokMinimumRequest;
use App\Models\BahanBaku;
use App\Models\PembelianStok;
use App\Models\User;
use App\Services\PembelianStokService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PembelianStokController extends Controller
{
    // Menampilkan riwayat pembelian stok
    public function index(Request $request): JsonResponse|View
    {
        $riwayat = PembelianStok::query()
            ->with([
                'bahan_baku',
                'dicatat_oleh_pengguna',
            ])
            ->orderByDesc('tanggal_pembelian')
            ->orderByDesc('id')
            ->paginate(15);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Riwayat pembelian stok berhasil diambil.',
                'data' => $riwayat,
            ]);
        }

        $semuaBahan = BahanBaku::orderBy('nama')->get();

        return view('manajer.pembelian-stok', compact('riwayat', 'semuaBahan'));
    }

    // Mencatat pembelian dan menambah stok
    public function store(
        StorePembelianStokRequest $request,
        PembelianStokService $service
    ): JsonResponse|RedirectResponse {
        /** @var User $pengguna */
        $pengguna = $request->user();

        $pembelian = $service->catat(
            $request->validated(),
            $pengguna
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Pembelian stok berhasil dicatat.',
                'data' => $pembelian,
            ], 201);
        }

        return redirect()->route('manajer.pembelian-stok.index')
            ->with('success', "Pembelian stok {$pembelian->bahan_baku?->nama} berhasil dicatat dan stok gudang telah bertambah.");
    }

    // Menampilkan bahan baku yang stoknya menipis
    public function stokMenipis(Request $request): JsonResponse|View
    {
        $data = BahanBaku::query()
            ->whereColumn('jumlah_stok', '<=', 'stok_minimum')
            ->orderBy('jumlah_stok')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Data stok menipis berhasil diambil.',
                'data' => $data,
            ]);
        }

        return view('manajer.stok-menipis', compact('data'));
    }

    // Mengubah batas minimum stok suatu bahan baku
    public function updateStokMinimum(
        UpdateStokMinimumRequest $request,
        BahanBaku $bahanBaku
    ): JsonResponse|RedirectResponse {
        $bahanBaku->update(
            $request->validated()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Batas minimum stok berhasil diperbarui.',
                'data' => $bahanBaku->refresh(),
            ]);
        }

        return redirect()->back()
            ->with('success', "Batas minimum stok {$bahanBaku->nama} berhasil diperbarui.");
    }
}
