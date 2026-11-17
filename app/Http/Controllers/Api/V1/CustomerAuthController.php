<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    /**
     * Registrasi pelanggan.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([

            'nama' => [
                'required',
                'string',
                'max:100',
            ],

            'nomor_telepon' => [
                'required',
                'string',
                'regex:/^[0-9]{10,20}$/',
                'unique:users,nomor_telepon',
            ],

            'kata_sandi' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

        ]);

        // Peran pelanggan ditetapkan oleh server,
        // bukan diambil dari permintaan aplikasi Android.
        $pelanggan = User::create([

            'nama' => $validated['nama'],

            'nomor_telepon' =>
                $validated['nomor_telepon'],

            'nama_pengguna' => null,

            'email' => null,

            'kata_sandi' =>
                $validated['kata_sandi'],

            'peran' => User::PERAN_PELANGGAN,

            'wajib_ganti_kata_sandi' => false,

        ]);

        // Buat token untuk Android.
        $token = $pelanggan
            ->createToken('android-kopi-senja')
            ->plainTextToken;

        return response()->json([

            'message' => 'Registrasi berhasil.',

            'token' => $token,

            'token_type' => 'Bearer',

            'user' => [
                'id' => $pelanggan->id,
                'nama' => $pelanggan->nama,
                'nomor_telepon' => $pelanggan->nomor_telepon,
                'peran' => $pelanggan->peran,
            ],

        ], 201);
    }

    /**
     * Login pelanggan.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([

            'nomor_telepon' => [
                'required',
                'string',
            ],

            'kata_sandi' => [
                'required',
                'string',
            ],

        ]);

        // Cari berdasarkan nomor HP dan pastikan
        // akun benar-benar memiliki peran pelanggan.
        $pelanggan = User::query()
            ->where(
                'nomor_telepon',
                $validated['nomor_telepon']
            )
            ->where(
                'peran',
                User::PERAN_PELANGGAN
            )
            ->first();

        // Periksa password.
        if (
            !$pelanggan ||
            !Hash::check(
                $validated['kata_sandi'],
                $pelanggan->kata_sandi
            )
        ) {

            return response()->json([
                'message' => 'Nomor HP atau password salah.',
            ], 401);
        }

        $token = $pelanggan
            ->createToken('android-kopi-senja')
            ->plainTextToken;

        return response()->json([

            'message' => 'Login berhasil.',

            'token' => $token,

            'token_type' => 'Bearer',

            'wajib_ganti_kata_sandi' =>
                (bool) $pelanggan->wajib_ganti_kata_sandi,

            'user' => [
                'id' => $pelanggan->id,
                'nama' => $pelanggan->nama,
                'nomor_telepon' => $pelanggan->nomor_telepon,
                'peran' => $pelanggan->peran,
            ],

        ]);
    }

    /**
     * Logout pelanggan.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()
            ->currentAccessToken()
            ?->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Melihat profil pelanggan yang sedang login.
     */
    public function profile(Request $request): JsonResponse
    {
        $pelanggan = $request->user();

        return response()->json([
            'user' => [
                'id' => $pelanggan->id,
                'nama' => $pelanggan->nama,
                'nomor_telepon' => $pelanggan->nomor_telepon,
                'peran' => $pelanggan->peran,
                'wajib_ganti_kata_sandi' =>
                    (bool) $pelanggan->wajib_ganti_kata_sandi,
            ],
        ]);
    }

    /**
     * Mengganti password pelanggan.
     */
    public function updatePassword(Request $request): JsonResponse
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
                'confirmed',
            ],

        ]);

        $pelanggan = $request->user();

        if (!Hash::check(
            $validated['kata_sandi_lama'],
            $pelanggan->kata_sandi
        )) {

            return response()->json([
                'message' => 'Password lama tidak sesuai.',
            ], 422);
        }

        $pelanggan->kata_sandi =
            $validated['kata_sandi_baru'];

        $pelanggan->wajib_ganti_kata_sandi = false;

        $pelanggan->save();

        return response()->json([
            'message' => 'Password berhasil diperbarui.',
        ]);
    }
}
