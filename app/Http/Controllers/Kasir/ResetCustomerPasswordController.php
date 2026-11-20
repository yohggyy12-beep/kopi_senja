<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResetCustomerPasswordController extends Controller
{
    /**
     * Menampilkan halaman pencarian akun pelanggan.
     */
    public function index(Request $request): View
    {
        $pelanggan = null;
        $nomorTelepon = trim($request->input('nomor_telepon', ''));

        if (!empty($nomorTelepon)) {
            // Bersihkan format nomor HP (hapus spasi, tanda minus)
            $cleanNomor = preg_replace('/[^0-9]/', '', $nomorTelepon);

            $pelanggan = User::query()
                ->where('peran', User::PERAN_PELANGGAN)
                ->where(function ($q) use ($cleanNomor, $nomorTelepon) {
                    $q->where('nomor_telepon', $cleanNomor)
                      ->orWhere('nomor_telepon', $nomorTelepon)
                      ->orWhere('nama_pengguna', $cleanNomor);
                })
                ->first();
        }

        return view('kasir.reset-password-pelanggan', compact('pelanggan', 'nomorTelepon'));
    }

    /**
     * Mereset password pelanggan dan menghasilkan password sementara.
     */
    public function reset(User $pelanggan): RedirectResponse
    {
        abort_unless(
            $pelanggan->peran === User::PERAN_PELANGGAN,
            404
        );

        $passwordSementara = Str::random(12);

        $pelanggan->kata_sandi = $passwordSementara;
        $pelanggan->wajib_ganti_kata_sandi = true;
        $pelanggan->save();

        // Cabut seluruh token Sanctum aktif pelanggan
        $pelanggan->tokens()->delete();

        return back()->with([
            'password_sementara' => $passwordSementara,
            'nama_pelanggan' => $pelanggan->nama,
            'nomor_pelanggan' => $pelanggan->nomor_telepon,
            'success' => "Password untuk pelanggan {$pelanggan->nama} berhasil direset."
        ]);
    }
}
