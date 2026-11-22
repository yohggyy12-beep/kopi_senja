<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manajer\SaveResepRequest;
use App\Models\BahanBaku;
use App\Models\Menu;
use App\Services\ResepService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResepController extends Controller
{
    protected ResepService $resepService;

    public function __construct(ResepService $resepService)
    {
        $this->resepService = $resepService;
    }

    /**
     * Menampilkan daftar menu beserta resep.
     */
    public function index(Request $request): JsonResponse|View|RedirectResponse
    {
        if ($request->wantsJson()) {
            $menus = $this->resepService->paginate();

            return response()->json($menus);
        }

        $pertama = Menu::orderBy('nama')->first();

        if ($pertama) {
            return redirect()->route(
                'manajer.resep.show',
                $pertama->id
            );
        }

        return view('manajer.resep', [
            'menu' => null,
            'allMenus' => collect(),
            'semuaBahan' => collect(),
        ]);
    }

    /**
     * Menampilkan komposisi resep dari satu menu.
     */
    public function show(Menu $menu, Request $request): JsonResponse|View
    {
        $hasil = $this->resepService->show($menu);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Resep berhasil ditemukan.',
                'data' => $hasil,
            ]);
        }

        $allMenus = Menu::orderBy('nama')->get();
        $semuaBahan = BahanBaku::orderBy('nama')->get();

        return view('manajer.resep', [
            'menu' => $hasil,
            'allMenus' => $allMenus,
            'semuaBahan' => $semuaBahan,
        ]);
    }

    /**
     * Menambahkan satu bahan melalui form Blade.
     */
    public function store(
        Request $request,
        Menu $menu
    ): JsonResponse|RedirectResponse {
        if (!$request->wantsJson() && $request->has('bahan_baku_id')) {
            $request->validate([
                'bahan_baku_id' => 'required|exists:bahan_baku,id',
                'jumlah_per_porsi' => 'required|numeric|gt:0',
            ]);

            $existing = $menu->detailResep->map(fn ($d) => [
                'bahan_baku_id' => $d->bahan_baku_id,
                'jumlah_per_porsi' => $d->jumlah_per_porsi,
            ])->toArray();

            // Mencegah bahan yang sama ditambahkan dua kali.
            $bahanBaruId = (int) $request->input('bahan_baku_id');

            foreach ($existing as $item) {
                if ((int) $item['bahan_baku_id'] === $bahanBaruId) {
                    return redirect()->back()->withErrors([
                        'bahan_baku_id' =>
                            'Bahan ini sudah ada di resep menu ini.',
                    ]);
                }
            }

            $existing[] = [
                'bahan_baku_id' => $bahanBaruId,
                'jumlah_per_porsi' =>
                    (float) $request->input('jumlah_per_porsi'),
            ];

            $this->resepService->save($menu, $existing);

            return redirect()
                ->route('manajer.resep.show', $menu->id)
                ->with('success', 'Bahan berhasil ditambahkan ke resep.');
        }

        // Request JSON menggunakan validasi Form Request.
        // Route API diarahkan ke storeApi().
        $hasil = $this->resepService->create(
            $menu,
            $request->all()
        );

        return response()->json([
            'message' => 'Resep berhasil dibuat.',
            'data' => $hasil,
        ], 201);
    }

    /**
     * Membuat resep melalui request JSON.
     */
    public function storeApi(
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
     * Menghapus satu bahan dari komposisi resep.
     */
    public function hapusBahan(
        Menu $menu,
        BahanBaku $bahanBaku
    ): RedirectResponse {
        $existing = $menu->detailResep
            ->where('bahan_baku_id', '!=', $bahanBaku->id)
            ->map(fn ($d) => [
                'bahan_baku_id' => $d->bahan_baku_id,
                'jumlah_per_porsi' => $d->jumlah_per_porsi,
            ])
            ->values()
            ->toArray();

        $this->resepService->save($menu, $existing);

        return redirect()
            ->route('manajer.resep.show', $menu->id)
            ->with(
                'success',
                "Bahan '{$bahanBaku->nama}' dihapus dari resep."
            );
    }

    /**
     * Memperbarui seluruh komposisi resep.
     */
    public function update(
        SaveResepRequest $request,
        Menu $menu
    ): JsonResponse|RedirectResponse {
        $hasil = $this->resepService->update(
            $menu,
            $request->validated()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Resep berhasil diperbarui.',
                'data' => $hasil,
            ]);
        }

        return redirect()
            ->route('manajer.resep.show', $menu->id)
            ->with('success', 'Resep berhasil diperbarui.');
    }

    /**
     * Menghapus seluruh komposisi resep dari satu menu.
     */
    public function destroy(
        Menu $menu,
        Request $request
    ): JsonResponse|RedirectResponse {
        $this->resepService->delete($menu);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Resep berhasil dihapus.',
            ]);
        }

        return redirect()
            ->route('manajer.resep.show', $menu->id)
            ->with('success', 'Resep berhasil dihapus.');
    }
}
