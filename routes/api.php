<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CustomerAuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:5,1');

    // 1. Grup untuk Logout (Wajib login & role pelanggan, tapi TIDAK diblokir password.change agar tetap bisa logout)
    Route::middleware([
        'auth:sanctum',
        'role:pelanggan',
    ])->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout']);
    });

    // 2. Grup untuk Endpoint /me dan /password (Dilindungi auth:sanctum dan role:pelanggan, TANPA password.change)
    Route::middleware([
        'auth:sanctum',
        'role:pelanggan',
    ])->group(function () {
        Route::get('/me', [CustomerAuthController::class, 'profile']);
        Route::put('/password', [CustomerAuthController::class, 'updatePassword']);
    });

});
