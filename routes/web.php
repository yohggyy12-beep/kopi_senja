<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\StaffAuthController;
use App\Http\Controllers\Auth\StaffPasswordController;
use App\Http\Controllers\Kasir\DashboardController as KasirDashboardController;
use App\Http\Controllers\Kasir\PesananController;
use App\Http\Controllers\Kasir\ResetCustomerPasswordController;
use App\Http\Controllers\Manajer\DashboardController;
use App\Http\Controllers\Manajer\BahanBakuController;
use App\Http\Controllers\Manajer\MenuController;
use App\Http\Controllers\Manajer\ResepController;
use App\Http\Controllers\Manajer\PembelianStokController;
use App\Http\Controllers\Manajer\HargaBahanController;
use App\Http\Controllers\Manajer\LaporanController;


/*
|--------------------------------------------------------------------------
| HALAMAN UMUM
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| ROUTE UNTUK SEMUA STAFF YANG SUDAH LOGIN
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [
        StaffAuthController::class,
        'destroy'
    ])->name('logout');

    // Ganti password
    Route::get('/change-password', [
        StaffPasswordController::class,
        'edit'
    ])->name('password.edit');

    Route::put('/change-password', [
        StaffPasswordController::class,
        'update'
    ])->name('password.update');

});


/*
|--------------------------------------------------------------------------
| ROUTE MANAJER
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:manager',
])
    ->prefix('manajer')
    ->name('manajer.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            DashboardController::class,
            'index'
        ])->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | MANAJEMEN BAHAN BAKU
        |--------------------------------------------------------------------------
        */

        Route::get('/bahan-baku', [
            BahanBakuController::class,
            'index'
        ])->name('bahan-baku.index');

        Route::post('/bahan-baku', [
            BahanBakuController::class,
            'store'
        ])->name('bahan-baku.store');

        Route::put('/bahan-baku/{bahanBaku}', [
            BahanBakuController::class,
            'update'
        ])->name('bahan-baku.update');

        Route::delete('/bahan-baku/{bahanBaku}', [
            BahanBakuController::class,
            'destroy'
        ])->name('bahan-baku.destroy');


        /*
        |--------------------------------------------------------------------------
        | HARGA SUPPLIER
        |--------------------------------------------------------------------------
        */

        Route::get('/harga-bahan', [
            HargaBahanController::class,
            'index'
        ])->name('harga-bahan.index');

        Route::post('/harga-bahan', [
            HargaBahanController::class,
            'perbarui'
        ])->name('harga-bahan.perbarui');


        /*
        |--------------------------------------------------------------------------
        | MANAJEMEN MENU
        |--------------------------------------------------------------------------
        */

        Route::apiResource(
            'menu',
            MenuController::class
        )->only([
            'index',
            'store',
            'update',
            'destroy',
        ]);


        /*
        |--------------------------------------------------------------------------
        | RESEP MELALUI ENDPOINT MENU
        |--------------------------------------------------------------------------
        |
        | Endpoint ini digunakan oleh Feature Test:
        | /manajer/menu/{menu}/resep
        |
        */

        Route::get('/menu/{menu}/resep', [
            ResepController::class,
            'show'
        ])->name('menu.resep.show');

        Route::post('/menu/{menu}/resep', [
            ResepController::class,
            'storeApi'
        ])->name('menu.resep.store');

        Route::put('/menu/{menu}/resep', [
            ResepController::class,
            'update'
        ])->name('menu.resep.update');

        Route::delete('/menu/{menu}/resep', [
            ResepController::class,
            'destroy'
        ])->name('menu.resep.destroy');


        /*
        |--------------------------------------------------------------------------
        | RESEP UNTUK HALAMAN BLADE YANG SUDAH ADA
        |--------------------------------------------------------------------------
        |
        | Route lama ini dipertahankan agar navigasi
        | dan form Manajemen Resep tidak rusak.
        |
        */

        Route::get('/resep', [
            ResepController::class,
            'index'
        ])->name('resep.index');

        Route::get('/resep/{menu}', [
            ResepController::class,
            'show'
        ])->name('resep.show');

        Route::post('/resep/{menu}', [
            ResepController::class,
            'store'
        ])->name('resep.store');

        Route::put('/resep/{menu}', [
            ResepController::class,
            'update'
        ])->name('resep.update');

        Route::delete('/resep/{menu}', [
            ResepController::class,
            'destroy'
        ])->name('resep.destroy');

        Route::delete('/resep/{menu}/bahan/{bahanBaku}', [
            ResepController::class,
            'hapusBahan'
        ])->name('resep.hapus-bahan');


        /*
        |--------------------------------------------------------------------------
        | PEMBELIAN STOK
        |--------------------------------------------------------------------------
        */

        Route::get('/pembelian-stok', [
            PembelianStokController::class,
            'index'
        ])->name('pembelian-stok.index');

        Route::post('/pembelian-stok', [
            PembelianStokController::class,
            'store'
        ])->name('pembelian-stok.store');


        /*
        |--------------------------------------------------------------------------
        | STOK MENIPIS DAN STOK MINIMUM
        |--------------------------------------------------------------------------
        */

        Route::get('/stok-menipis', [
            PembelianStokController::class,
            'stokMenipis'
        ])->name('stok-menipis.index');

        Route::put('/bahan-baku/{bahanBaku}/stok-minimum', [
            PembelianStokController::class,
            'updateStokMinimum'
        ])->name('bahan-baku.stok-minimum.update');


        /*
        |--------------------------------------------------------------------------
        | HPP DAN LAPORAN
        |--------------------------------------------------------------------------
        */

        Route::get('/hpp-margin', [
            LaporanController::class,
            'hppMargin'
        ])->name('laporan.hpp-margin');

        Route::get('/laporan-penjualan', [
            LaporanController::class,
            'penjualan'
        ])->name('laporan.penjualan');

        Route::get('/laporan-persediaan', [
            LaporanController::class,
            'persediaan'
        ])->name('laporan.persediaan');

    });


/*
|--------------------------------------------------------------------------
| ROUTE KASIR
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'role:kasir',
])
    ->prefix('kasir')
    ->name('kasir.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD KASIR
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            KasirDashboardController::class,
            'index'
        ])->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | RESET PASSWORD PELANGGAN
        |--------------------------------------------------------------------------
        */

        Route::get('/reset-password-pelanggan', [
            ResetCustomerPasswordController::class,
            'index'
        ])->name('pelanggan.reset-page');

        Route::post('/pelanggan/{pelanggan}/reset-password', [
            ResetCustomerPasswordController::class,
            'reset'
        ])->name('pelanggan.reset-password');


        /*
        |--------------------------------------------------------------------------
        | PEMESANAN KASIR
        |--------------------------------------------------------------------------
        */

        Route::get('/pesanan/create', [
            PesananController::class,
            'create'
        ])->name('pesanan.create');

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


        /*
        |--------------------------------------------------------------------------
        | STRUK DAN DETAIL PESANAN
        |--------------------------------------------------------------------------
        |
        | Route struk harus diletakkan sebelum route
        | detail /pesanan/{pesanan}.
        |
        */

        Route::get('/pesanan/{pesanan}/struk', [
            PesananController::class,
            'struk'
        ])->name('pesanan.struk');

        Route::get('/pesanan/{pesanan}', [
            PesananController::class,
            'show'
        ])->name('pesanan.show');


        /*
        |--------------------------------------------------------------------------
        | STATUS DAN PEMBATALAN PESANAN
        |--------------------------------------------------------------------------
        */

        Route::put('/pesanan/{pesanan}/status', [
            PesananController::class,
            'updateStatus'
        ])->name('pesanan.status');

        Route::delete('/pesanan/{pesanan}', [
            PesananController::class,
            'destroy'
        ])->name('pesanan.destroy');

    });
