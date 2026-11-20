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

class PembelianStokController extends Controller
{
    // Menampilkan riwayat pembelian stok
    public function index(): JsonResponse
    {
        $riwayat = PembelianStok::query()
            ->with([
                'bahan_baku',
                'dicatat_oleh_pengguna',
            ])
            ->orderByDesc('tanggal_pembelian')
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json([
            'message' => 'Riwayat pembelian stok berhasil diambil.',
            'data' => $riwayat,
        ]);
    }

    // Mencatat pembelian dan menambah stok
    public function store(
        StorePembelianStokRequest $request,
        PembelianStokService $service
    ): JsonResponse {

        /** @var User $pengguna */
        $pengguna = $request->user();

        $pembelian = $service->catat(
            $request->validated(),
            $pengguna
        );

        return response()->json([
            'message' => 'Pembelian stok berhasil dicatat.',
            'data' => $pembelian,
        ], 201);
    }

    // Menampilkan bahan baku yang stoknya menipis
    public function stokMenipis(): JsonResponse
    {
        $data = BahanBaku::query()
            ->whereColumn('jumlah_stok', '<=', 'stok_minimum')
            ->orderBy('jumlah_stok')
            ->get();

        return response()->json([
            'message' => 'Data stok menipis berhasil diambil.',
            'data' => $data,
        ]);
    }

    // Mengubah batas minimum stok suatu bahan baku
    public function updateStokMinimum(
        UpdateStokMinimumRequest $request,
        BahanBaku $bahanBaku
    ): JsonResponse {

        $bahanBaku->update(
            $request->validated()
        );

        return response()->json([
            'message' => 'Batas minimum stok berhasil diperbarui.',
            'data' => $bahanBaku->refresh(),
        ]);
    }
}
