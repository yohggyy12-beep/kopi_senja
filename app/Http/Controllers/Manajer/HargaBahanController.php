<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SupplierHargaService;
use Illuminate\Http\JsonResponse;

class HargaBahanController extends Controller
{
    public function perbarui(
        SupplierHargaService $service
    ): JsonResponse{
        $hasil = $service->perbaruiHarga();
        return response()->json([
            'masage' => $hasil['pesan'],
            'data' => $hasil,
        ]);
    }
}
