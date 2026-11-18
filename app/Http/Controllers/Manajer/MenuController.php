<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;

use App\Http\Requests\Manajer\StoreMenuRequest;
use App\Http\Requests\Manajer\UpdateMenuRequest;

use App\Models\Menu;

use App\Services\MenuService;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    protected MenuService $menuService;

    public function __construct(
        MenuService $menuService
    ) {
        $this->menuService = $menuService;
    }

    /**
     * Menampilkan daftar menu.
     */
    public function index(Request $request): JsonResponse
    {
        $menus = $this->menuService->paginate(
            $request->only([
                'q',
                'kategori',
                'status',
            ])
        );

        return response()->json($menus);
    }

    /**
     * Membuat menu baru.
     */
    public function store(
        StoreMenuRequest $request
    ): JsonResponse {

        $menu = $this->menuService->create(
            $request->validated()
        );

        return response()->json([
            'message' => 'Menu berhasil ditambahkan.',
            'data' => $menu,
        ], 201);
    }

    /**
     * Memperbarui menu.
     */
    public function update(
        UpdateMenuRequest $request,
        Menu $menu
    ): JsonResponse {

        $hasil = $this->menuService->update(
            $menu,
            $request->validated()
        );

        return response()->json([
            'message' => 'Menu berhasil diperbarui.',
            'data' => $hasil,
        ]);
    }

    /**
     * Menghapus menu.
     */
    public function destroy(
        Menu $menu
    ): JsonResponse {

        $this->menuService->delete($menu);

        return response()->json([
            'message' => 'Menu berhasil dihapus.',
        ]);
    }
}
