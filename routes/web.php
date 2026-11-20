<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\StaffAuthController;

use App\Http\Controllers\Auth\StaffPasswordController;
use App\Http\Controllers\Kasir\ResetCustomerPasswordController;
use App\Http\Controllers\Manajer\BahanBakuController;
use App\Http\Controllers\Manajer\MenuController;
use App\Http\Controllers\Manajer\ResepController;
use App\Http\Controllers\Manajer\PembelianStokController;
use App\Http\Controllers\Kasir\PesananController;
use App\Http\Controllers\Manajer\HargaBahanController;



Route::redirect('/', '/login');
Route::get('/login', [
    StaffAuthController::class,
    'create'
    ])->name('login');

Route::post('/login', [
    StaffAuthController::class,
    'store'
    ])
    ->middleware('throttle:5,1')
    ->name('login.store');

Route::middleware('auth')->group(function(){
    //Logout
    Route::post('/logout',[
        StaffAuthController::class,
        'destroy'
    ])->name('logout');

    Route::get('/change-password', [
        StaffPasswordController::class,
        'edit'
    ])->name('password.edit');

    Route::put('/change-password', [
        StaffPasswordController::class,
        'update'
    ])->name('password.update');

    //kasir
    Route::middleware('role:kasir')
    ->prefix('kasir')
    ->name('kasir.')
    ->group(function(){

        Route::get('/dashboard', function(){
            return response('kasir.dashboard');
        })->name('dashboard');

        Route::post('/pelanggan/{pelanggan}/reset-password', [
            ResetCustomerPasswordController::class,
            'reset'
        ])->name('pelanggan.reset-password');
     });
        Route::middleware('role:manager')
        ->prefix('manajer')
        ->name('manajer.')
        ->group(function(){
            Route::get('/dashboard', function(){
                return response('manajer.dashboard');
            })->name('dashboard');
        });
    });

   Route::middleware([
    'auth',
    'role:manager',
])
    ->prefix('manajer')
    ->name('manajer.')
    ->group(function () {

     Route::get(
            '/bahan-baku',
            [BahanBakuController::class, 'index']
        )->name('bahan-baku.index');

        Route::post(
            '/bahan-baku',
            [BahanBakuController::class, 'store']
        )->name('bahan-baku.store');

        Route::put(
            '/bahan-baku/{bahanBaku}',
            [BahanBakuController::class, 'update']
        )->name('bahan-baku.update');

        Route::delete(
            '/bahan-baku/{bahanBaku}',
            [BahanBakuController::class, 'destroy']
        )->name('bahan-baku.destroy');

        Route::post(
            '/harga-bahan',
            [HargaBahanController::class, 'perbarui']
        )->name('harga-bahan.perbarui');

    });

    Route::middleware([
    'auth',
    'role:manager',
])
    ->prefix('manajer')
    ->name('manajer.')
    ->group(function () {

        // CRUD bahan baku yang sudah dibuat.
        // ...

        // CRUD menu.
        Route::apiResource(
            'menu',
            MenuController::class
        )->only([
            'index',
            'store',
            'update',
            'destroy',
        ]);

        // CRUD Resep
        Route::get('/resep', [ResepController::class, 'index'])->name('resep.index');
        Route::get('/resep/{menu}', [ResepController::class, 'show'])->name('resep.show');
        Route::post('/resep', [ResepController::class, 'store'])->name('resep.store');
        Route::put('/resep/{menu}', [ResepController::class, 'update'])->name('resep.update');
        Route::delete('/resep/{menu}', [ResepController::class, 'destroy'])->name('resep.destroy');


        Route::get(
    '/pembelian-stok',
    [PembelianStokController::class, 'index']
)->name('pembelian-stok.index');

Route::post(
    '/pembelian-stok',
    [PembelianStokController::class, 'store']
)->name('pembelian-stok.store');

Route::get(
    '/stok-menipis',
    [PembelianStokController::class, 'stokMenipis']
)->name('stok-menipis.index');

Route::put(
    '/bahan-baku/{bahanBaku}/stok-minimum',
    [PembelianStokController::class, 'updateStokMinimum']
)->name('bahan-baku.stok-minimum.update');
    });


    Route::middleware(['auth', 'role:kasir'])
    ->prefix('kasir')
    ->name('kasir.')
    ->group(function () {

        // Daftar pesanan
        Route::get('/pesanan', [
            PesananController::class,
            'index'
        ])->name('pesanan.index');

        // Membuat pesanan baru
        Route::post('/pesanan', [
            PesananController::class,
            'store'
        ])->name('pesanan.store');

        // Detail pesanan dan struk
        Route::get('/pesanan/{pesanan}', [
            PesananController::class,
            'show'
        ])->name('pesanan.show');

        // Mengubah status pesanan
        Route::put('/pesanan/{pesanan}/status', [
            PesananController::class,
            'updateStatus'
        ])->name('pesanan.status');

        // Membatalkan pesanan
        Route::delete('/pesanan/{pesanan}', [
            PesananController::class,
            'destroy'
        ])->name('pesanan.destroy');

    });
