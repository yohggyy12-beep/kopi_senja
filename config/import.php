<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lokasi Default File CSV
    |--------------------------------------------------------------------------
    */
    'paths' => [
        'bahan' => database_path('import/original/bahan.csv'),
        'menu' => database_path('import/original/menu.csv'),
        'resep' => database_path('import/original/resep.csv'),
        'kategori' => database_path('import/original/master_kategori_menu.csv'),
        'demo_stok' => database_path('import/original/seed_stok_demo.csv'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Daftar Padanan / Alias Nama Bahan Baku
    |--------------------------------------------------------------------------
    | 'Biji kopi arabica' merupakan variasi penulisan dari 'Biji kopi arabika'.
    | 'Susu oat' dan 'Sirup vanila' sengaja tidak dimasukkan ke daftar alias
    | agar dicatat sebagai bahan tidak dikenal dan menu ditandai belum lengkap.
    */
    'ingredient_aliases' => [
        'biji kopi arabica' => 'biji kopi arabika',
    ],

    // Alias untuk fleksibilitas
    'aliases' => [
        'biji kopi arabica' => 'biji kopi arabika',
    ],

];
