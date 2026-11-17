<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;


class AkunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(['nama_pengguna' => 'manager'], [
            'nama' => 'Rani', 'peran' => 'manager',
            'kata_sandi' => 'Manajer#123', 'wajib_ganti_kata_sandi' => false,
        ]);

        User::updateOrCreate(['nama_pengguna' => 'kasir'], [
            'nama' => 'Rudi', 'peran' => 'kasir',
            'kata_sandi' => 'Kasir#124', 'wajib_ganti_kata_sandi' => false,
        ]);

        User::updateOrCreate(['nama_pengguna' => '08123456789'], [
            'nama' => 'UjiPelanggan', 'peran' => 'pelanggan',
            'kata_sandi' => 'Pelanggan#125', 'wajib_ganti_kata_sandi' => false,
        ]);
    }
}

