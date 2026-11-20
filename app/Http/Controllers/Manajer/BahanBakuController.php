<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manajer\StoreBahanBakuRequest;
use App\Http\Requests\Manajer\UpdateBahanBakuRequest;
use App\Models\BahanBaku;
use App\Services\BahanBakuService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BahanBakuController extends Controller
{
    protected BahanBakuService $bahanBakuService;

    public function __construct(
        BahanBakuService $bahanBakuService
    ) {
        $this->bahanBakuService = $bahanBakuService;
    }

    public function index(Request $request): JsonResponse|View
    {
        $filters = $request->only(['q', 'status']);
        $bahanBakuList = $this->bahanBakuService->paginate($filters);

        if ($request->wantsJson()) {
            return response()->json($bahanBakuList);
        }

        $q = $request->input('q', '');
        $status = $request->input('status', 'semua');
        $editItem = null;
        if ($request->filled('edit')) {
            $editItem = BahanBaku::find($request->input('edit'));
        }

        return view('manajer.bahan-baku', compact('bahanBakuList', 'q', 'status', 'editItem'));
    }

    public function store(
        StoreBahanBakuRequest $request
    ): JsonResponse|RedirectResponse {
        $bahan = $this->bahanBakuService->create(
            $request->validated()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Bahan baku berhasil ditambahkan',
                'data' => $bahan,
            ], 201);
        }

        return redirect()->route('manajer.bahan-baku.index')
            ->with('success', "Bahan baku '{$bahan->nama}' berhasil ditambahkan.");
    }

    public function update(
        UpdateBahanBakuRequest $request,
        BahanBaku $bahanBaku
    ): JsonResponse|RedirectResponse {
        $hasil = $this->bahanBakuService->update(
            $bahanBaku,
            $request->validated()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Bahan baku berhasil diubah',
                'data' => $hasil
            ]);
        }

        return redirect()->route('manajer.bahan-baku.index')
            ->with('success', "Bahan baku '{$hasil->nama}' berhasil diperbarui.");
    }

    public function destroy(
        BahanBaku $bahanBaku,
        Request $request
    ): JsonResponse|RedirectResponse {
        $this->bahanBakuService->delete($bahanBaku);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Bahan baku berhasil dihapus.',
            ]);
        }

        return redirect()->route('manajer.bahan-baku.index')
            ->with('success', 'Bahan baku berhasil dihapus.');
    }
}
