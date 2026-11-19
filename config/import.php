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
    ],

    /*
    |--------------------------------------------------------------------------
    | Daftar Padanan / Alias Nama Bahan Baku
    |--------------------------------------------------------------------------
    | Memetakan variasi penulisan nama bahan (misal ejaan Inggris/Indonesia)
    | ke nama bahan baku resmi yang tersimpan di database.
    */
    'aliases' => [
        'biji kopi arabica' => 'biji kopi arabika',
    ],

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
];
