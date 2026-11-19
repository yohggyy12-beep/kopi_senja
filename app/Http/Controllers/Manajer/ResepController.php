<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manajer\SaveResepRequest;
use App\Models\Menu;
use App\Services\ResepService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResepController extends Controller
{
    protected ResepService $resepService;

    public function __construct(ResepService $resepService)
    {
        $this->resepService = $resepService;
    }

    /**
     * Menampilkan daftar menu beserta resepnya.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->resepService->paginate(
            $request->only(['q', 'resep_lengkap'])
        );

        return response()->json($data);
    }

    /**
     * Menampilkan detail resep untuk menu tertentu.
     */
    public function show(Menu $menu): JsonResponse
    {
        $data = $this->resepService->getByMenu($menu);

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Menyimpan resep untuk menu (menu_id di body request).
     */
    public function store(SaveResepRequest $request): JsonResponse
    {
        $menuId = $request->input('menu_id');
        $menu = Menu::findOrFail($menuId);

        $result = $this->resepService->save(
            $menu,
            $request->validated()['items']
        );

        return response()->json([
            'message' => 'Resep berhasil disimpan.',
            'data' => $result,
        ], 201);
    }

    /**
     * Memperbarui resep untuk menu (menu via route parameter).
     */
    public function update(SaveResepRequest $request, Menu $menu): JsonResponse
    {
        $result = $this->resepService->save(
            $menu,
            $request->validated()['items']
        );

        return response()->json([
            'message' => 'Resep berhasil diperbarui.',
            'data' => $result,
        ]);
    }

    /**
     * Menghapus resep dari menu tertentu.
     */
    public function destroy(Menu $menu): JsonResponse
    {
        $this->resepService->delete($menu);

        return response()->json([
            'message' => 'Resep berhasil dihapus.',
        ]);
    }
}
