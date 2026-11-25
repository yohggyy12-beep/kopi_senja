<?php

return [
HEAD


fa7bc80f4ea61204ec4726db2b17aae0029e23a2
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
 HEAD
        'demo_stok' => database_path('import/original/seed_stok_demo.csv'),

 fa7bc80f4ea61204ec4726db2b17aae0029e23a2
    ],

    /*
    |--------------------------------------------------------------------------
    | Daftar Padanan / Alias Nama Bahan Baku
    |--------------------------------------------------------------------------
<<<<<<< HEAD
    | 'Biji kopi arabica' merupakan variasi penulisan dari 'Biji kopi arabika'.
    | 'Susu oat' dan 'Sirup vanila' sengaja tidak dimasukkan ke daftar alias
    | agar dicatat sebagai bahan tidak dikenal dan menu ditandai belum lengkap.
    */
    'ingredient_aliases' => [
        'biji kopi arabica' => 'biji kopi arabika',
    ],

    // Alias untuk fleksibilitas
=======
    | Memetakan variasi penulisan nama bahan (misal ejaan Inggris/Indonesia)
    | ke nama bahan baku resmi yang tersimpan di database.
    */
>>>>>>> fa7bc80f4ea61204ec4726db2b17aae0029e23a2
    'aliases' => [
        'biji kopi arabica' => 'biji kopi arabika',
    ],

<<<<<<< HEAD
=======
    /*
    |--------------------------------------------------------------------------
    | Konversi Satuan ke Satuan Dasar
    |--------------------------------------------------------------------------
    | Mendefinisikan satuan dasar ('g', 'ml', 'pcs') dan faktor pengalinya.
    */
    'units' => [
        'kg' => [
            'base' => 'g',
            'factor' => 1000,
        ],
        'g' => [
            'base' => 'g',
            'factor' => 1,
        ],
        'liter' => [
            'base' => 'ml',
            'factor' => 1000,
        ],
        'l' => [
            'base' => 'ml',
            'factor' => 1000,
        ],
        'ml' => [
            'base' => 'ml',
            'factor' => 1,
        ],
        'pcs' => [
            'base' => 'pcs',
            'factor' => 1,
        ],
    ],
>>>>>>> fa7bc80f4ea61204ec4726db2b17aae0029e23a2
];
