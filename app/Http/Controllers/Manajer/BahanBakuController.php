<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;

use App\Http\Requests\Manajer\StoreBahanBakuRequest;
use App\Http\Requests\Manajer\UpdateBahanBakuRequest;
use App\Models\BahanBaku;
use App\Services\BahanBakuService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BahanBakuController extends Controller
{
    protected BahanBakuService $bahanBakuService;
    public function __construct(
        BahanBakuService $BahanBakuService
    ) {
        $this->bahanBakuService = $BahanBakuService;
    }

    public function index(Request $request): JsonResponse
    {
        $Bahanbaku = $this->bahanBakuService->paginate(
            $request->only(['q', 'status'])
        );
        return response()->json($Bahanbaku);
    }

    public function store(
        StoreBahanBakuRequest  $request
    ): JsonResponse {
        $Bahanbaku = $this->bahanBakuService->create(
            $request->validated()
        );
        return response()->json([
            'massage' => 'Bahan baku berhasil ditambahkan',
            'data' => $Bahanbaku,
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
        return Response()->json([
            'massage' => 'Bahan baku berhasil diubah',
            'data' => $hasil
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
