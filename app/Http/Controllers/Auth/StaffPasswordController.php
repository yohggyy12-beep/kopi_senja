<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

class StaffPasswordController extends Controller
{
    public function edit(): View
    {
        return view('auth.change-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kata_sandi_lama' => [
                'required',
                'string',
            ],
            'kata_sandi_baru' => [
                'required',
                'string',
                'min:8',
                'confirmed', // <-- Ini mewajibkan ada field pendamping bernama 'kata_sandi_baru_confirmation'
            ],
        ]);

        $user = $request->user();

        if (!Hash::check(
            $validated['kata_sandi_lama'],
            $user->kata_sandi
        )) {

            return back()->withErrors([
                'kata_sandi_lama' =>
                'Password lama tidak sesuai.',
            ]);
        }

        $user->kata_sandi =
            $validated['kata_sandi_baru'];


        $user->wajib_ganti_kata_sandi = false;
        $user->save();

        if ($user->peran === User::PERAN_MANAGER) {
            return redirect()->route('manajer.dashboard');
        }

        return redirect()->route('kasir.dashboard');
    }
}
