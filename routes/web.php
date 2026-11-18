<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\StaffAuthController;

use App\Http\Controllers\Auth\StaffPasswordController;
use App\Http\Controllers\Kasir\ResetCustomerPasswordController;
use App\Http\Controllers\Manajer\BahanBakuController;
use App\Http\Controllers\Manajer\MenuController;



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

    });

