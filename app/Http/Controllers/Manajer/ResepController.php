<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;

use App\Http\Requests\Manajer\SaveResepRequest;

use App\Models\Menu;

use App\Services\ResepService;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ResepController extends Controller
{
    protected ResepService $resepService;

    public function __construct(
        ResepService $resepService
    ) {
        $this->resepService = $resepService;
    }

    /**
     * Menampilkan daftar menu beserta resep.
     */
    public function index(): JsonResponse
    {
        $menus = $this->resepService->paginate();

        return response()->json($menus);
    }

    /**
     * Melihat komposisi resep suatu menu.
     */
    public function show(Menu $menu): JsonResponse
    {
        $hasil = $this->resepService->show($menu);

        return response()->json([
            'message' => 'Resep berhasil ditemukan.',
            'data' => $hasil,
        ]);
    }

    /**
     * Membuat resep baru.
     */
    public function store(
        SaveResepRequest $request,
        Menu $menu
    ): JsonResponse {

        $hasil = $this->resepService->create(
            $menu,
            $request->validated()
        );

        return response()->json([
            'message' => 'Resep berhasil dibuat.',
            'data' => $hasil,
        ], 201);
    }

    /**
     * Memperbarui komposisi resep.
     */
    public function update(
        SaveResepRequest $request,
        Menu $menu
    ): JsonResponse {

        $hasil = $this->resepService->update(
            $menu,
            $request->validated()
        );

        return response()->json([
            'message' => 'Resep berhasil diperbarui.',
            'data' => $hasil,
        ]);
    }

    /**
     * Menghapus resep.
     */
    public function destroy(Menu $menu): JsonResponse
    {
        $this->resepService->delete($menu);

        return response()->json([
            'message' => 'Resep berhasil dihapus.',
        ]);
    }
}
