<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePesananRequest;
use App\Http\Requests\UpdateStatusPesananRequest;
use App\Models\DetailPesanan;
use App\Models\Pesanan;
use App\Services\PesananService;
use Illuminate\Http\JsonResponse;

class PesananController extends Controller
{
    /**
     * Menampilkan daftar pesanan.
     */
    public function index(): JsonResponse
    {
        $pesanan = Pesanan::query()
            ->with('dibuatOlehPengguna')
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'message' => 'Daftar pesanan berhasil diambil.',
            'data' => $pesanan,
        ]);
    }


    /**
     * Menyimpan pesanan baru.
     */
    public function store(
        StorePesananRequest $request,
        PesananService $service
    ): JsonResponse {

        $pesanan = $service->buatPesanan(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Pesanan berhasil dibuat.',
            'data' => $pesanan,
        ], 201);
    }


    /**
     * Menampilkan detail pesanan dan struk.
     */
    public function show(Pesanan $pesanan): JsonResponse
    {
        $pesanan->load([
            'dibuatOlehPengguna',
            'detailPesanan.menu',
            'detailPesanan.detailBahanPesanan.bahanBaku',
        ]);

        return response()->json([
            'message' => 'Detail pesanan berhasil diambil.',
            'data' => $pesanan,
        ]);
    }


    /**
     * Mengubah status pesanan.
     */
    public function updateStatus(
        UpdateStatusPesananRequest $request,
        Pesanan $pesanan,
        PesananService $service
    ): JsonResponse {

        $hasil = $service->ubahStatus(
            $pesanan,
            $request->validated('status')
        );

        return response()->json([
            'message' => 'Status pesanan berhasil diperbarui.',
            'data' => $hasil,
        ]);
    }


    /**
     * Membatalkan pesanan.
     */
    public function destroy(
        Pesanan $pesanan,
        PesananService $service
    ): JsonResponse {

        $hasil = $service->batalkanPesanan($pesanan);

        return response()->json([
            'message' => 'Pesanan berhasil dibatalkan dan stok dikembalikan.',
            'data' => $hasil,
        ]);
    }
}
