<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil AkunSeeder Anda di sini
        $this->call([
            AkunSeeder::class,
        ]);
    }
}
