<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manajer\StoreBahanBakuRequest;
use App\Http\Requests\Manajer\UpdateBahanBakuRequest;
use App\Models\BahanBaku;
use App\Services\BahanBakuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    protected BahanBakuService $bahanBakuService;

    public function __construct(
        BahanBakuService $bahanBakuService
    ) {
        $this->bahanBakuService = $bahanBakuService;
    }

    public function index(Request $request): JsonResponse
    {
        $bahanBaku = $this->bahanBakuService->paginate(
            $request->only(['q', 'status'])
        );

        return response()->json($bahanBaku);
    }

    public function store(
        StoreBahanBakuRequest $request
    ): JsonResponse {
        $bahanBaku = $this->bahanBakuService->create(
            $request->validated()
        );

        return response()->json([
            'message' => 'Bahan baku berhasil ditambahkan',
            'data' => $bahanBaku,
        ], 201);
    }

    public function update(
        UpdateBahanBakuRequest $request,
        BahanBaku $bahanBaku
    ): JsonResponse {
        $hasil = $this->bahanBakuService->update(
            $bahanBaku,
            $request->validated()
        );

        return response()->json([
            'message' => 'Bahan baku berhasil diubah',
            'data' => $hasil,
        ]);
    }

    public function destroy(
        BahanBaku $bahanBaku
    ): JsonResponse {

        $this->bahanBakuService->delete($bahanBaku);

        return response()->json([
            'message' => 'Bahan baku berhasil dihapus.',
        ]);
    }
}
