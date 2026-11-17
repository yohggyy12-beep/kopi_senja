<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ResetCustomerPasswordController extends Controller
{
    public function reset(User $pelanggan): RedirectResponse
    {
        abort_unless(
            $pelanggan->peran === User::PERAN_PELANGGAN,
            404
        );
        $passwordSementara = Str::random(12);
        $pelanggan->kata_sandi = $passwordSementara;
        $pelanggan->save();
        $pelanggan->tokens()->delete();
        return back()->with(
            'password_sementara',
            $passwordSementara
        );
    }


}
