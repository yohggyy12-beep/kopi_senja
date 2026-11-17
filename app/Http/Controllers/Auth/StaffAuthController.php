<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class StaffAuthController extends Controller
{

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'nama_pengguna' => [
                'required',
                'string',
                'max:100',
            ],

            'kata_sandi' => [
                'required',
                'string',
            ],
        ]);


        $berhasil = Auth::guard('web')->attempt([
            'nama_pengguna' => $validated['nama_pengguna'],


            'password' => $validated['kata_sandi'],

        ]);

        if (!$berhasil) {

            return back()
                ->withErrors([
                    'nama_pengguna' =>
                        'Nama pengguna atau password salah.',
                ])
                ->onlyInput('nama_pengguna');
        }

        $user = Auth::guard('web')->user();

        if (!in_array($user->peran, [
            User::PERAN_MANAGER,
            User::PERAN_KASIR,
        ], true)) {

            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'nama_pengguna' =>
                    'Akun ini tidak dapat digunakan untuk login web.',
            ]);
        }

        $request->session()->regenerate();

        if ($user->wajib_ganti_kata_sandi) {
            return redirect()->route('password.edit');
        }
        if ($user->peran === User::PERAN_MANAGER) {
            return redirect()->route('manajer.dashboard');
        }

        return redirect()->route('kasir.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
