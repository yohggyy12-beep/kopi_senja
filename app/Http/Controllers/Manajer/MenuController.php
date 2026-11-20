<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manajer\StoreMenuRequest;
use App\Http\Requests\Manajer\UpdateMenuRequest;
use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
    public function index(Request $request): JsonResponse|View
    {
        $filters = $request->only([
            'q',
            'kategori',
            'status',
        ]);

        $menus = $this->menuService->paginate($filters);

        if ($request->wantsJson()) {
            return response()->json($menus);
        }

        $q = $request->input('q', '');
        $kategori = $request->input('kategori', '');
        $status = $request->input('status', '');

        $editItem = null;
        if ($request->filled('edit')) {
            $editItem = Menu::find($request->input('edit'));
        }

        $kategoriOptions = ['Minuman', 'Makanan'];

        return view('manajer.menu', compact('menus', 'q', 'kategori', 'status', 'editItem', 'kategoriOptions'));
    }

    /**
     * Membuat menu baru.
     */
    public function store(
        StoreMenuRequest $request
    ): JsonResponse|RedirectResponse {
        $menu = $this->menuService->create(
            $request->validated()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Menu berhasil ditambahkan.',
                'data' => $menu,
            ], 201);
        }

        return redirect()->route('manajer.menu.index')
            ->with('success', "Menu '{$menu->nama}' berhasil ditambahkan.");
    }

    /**
     * Memperbarui menu.
     */
    public function update(
        UpdateMenuRequest $request,
        Menu $menu
    ): JsonResponse|RedirectResponse {
        $hasil = $this->menuService->update(
            $menu,
            $request->validated()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Menu berhasil diperbarui.',
                'data' => $hasil,
            ]);
        }

        return redirect()->route('manajer.menu.index')
            ->with('success', "Menu '{$hasil->nama}' berhasil diperbarui.");
    }

    /**
     * Menghapus menu.
     */
    public function destroy(
        Menu $menu,
        Request $request
    ): JsonResponse|RedirectResponse {
        $this->menuService->delete($menu);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Menu berhasil dihapus.',
            ]);
        }

        return redirect()->route('manajer.menu.index')
            ->with('success', 'Menu berhasil dihapus.');
    }
}
