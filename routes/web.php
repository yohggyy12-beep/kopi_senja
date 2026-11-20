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
use App\Http\Controllers\Manajer\DashboardController;



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

Route::middleware('auth')->group(function () {
    //Logout
    Route::post('/logout', [
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
        ->group(function () {

            Route::get('/dashboard', [
                \App\Http\Controllers\Kasir\DashboardController::class,
                'index'
            ])->name('dashboard');

            Route::post('/pelanggan/{pelanggan}/reset-password', [
                ResetCustomerPasswordController::class,
                'reset'
            ])->name('pelanggan.reset-password');
        });
    Route::middleware('role:manager')
        ->prefix('manajer')
        ->name('manajer.')
        ->group(function () {
            Route::get('/dashboard', [
                \App\Http\Controllers\Manajer\DashboardController::class,
                'index'
            ])->name('dashboard');
        });
});

Route::middleware([
    'auth',
    'role:manager',
])
    ->prefix('manajer')
    ->name('manajer.')
    ->group(function () {

        Route::get('/dashboard', [
            DashboardController::class,
            'index'
        ])->name('dashboard');

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

        Route::get(
            '/harga-bahan',
            [HargaBahanController::class, 'index']
        )->name('harga-bahan.index');

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
        Route::post('/resep/{menu}', [ResepController::class, 'store'])->name('resep.store');
        Route::delete('/resep/{menu}/bahan/{bahanBaku}', [ResepController::class, 'hapusBahan'])->name('resep.hapus-bahan');
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

        // Analitik & Laporan
        Route::get('/hpp-margin', [\App\Http\Controllers\Manajer\LaporanController::class, 'hppMargin'])->name('laporan.hpp-margin');
        Route::get('/laporan-penjualan', [\App\Http\Controllers\Manajer\LaporanController::class, 'penjualan'])->name('laporan.penjualan');
        Route::get('/laporan-persediaan', [\App\Http\Controllers\Manajer\LaporanController::class, 'persediaan'])->name('laporan.persediaan');
    });


Route::middleware(['auth', 'role:kasir'])
    ->prefix('kasir')
    ->name('kasir.')
    ->group(function () {

        // Buat pesanan baru (halaman POS)
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

        // Struk pesanan (didefinisikan sebelum {pesanan} jika perlu atau bersama)
        Route::get('/pesanan/{pesanan}/struk', [
            PesananController::class,
            'struk'
        ])->name('pesanan.struk');

        // Detail pesanan
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

        // Halaman Reset Password Pelanggan
        Route::get('/reset-password-pelanggan', [
            ResetCustomerPasswordController::class,
            'index'
        ])->name('pelanggan.reset-page');
    });
