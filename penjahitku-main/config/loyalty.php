<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Point to Currency Conversion Rate
    |--------------------------------------------------------------------------
    |
    | Nilai tukar 1 poin dalam Rupiah untuk diskon checkout.
    | 
    | Rumus perhitungan:
    | - Setiap belanja Rp 5.000 = 1 poin
    | - 1 poin = Rp 50 nilai diskon
    | - 100 poin = Rp 5.000 diskon (setara pulsa Rp 5.000)
    |
    | Ini setara dengan cashback 1% untuk pelanggan loyal.
    |
    */
    'point_to_currency' => env('LOYALTY_POINT_TO_CURRENCY', 50),

    /*
    |--------------------------------------------------------------------------
    | Rupiah per Point (untuk kalkulasi pemberian poin)
    |--------------------------------------------------------------------------
    |
    | Berapa Rupiah belanja untuk mendapat 1 poin.
    | Default: Rp 5.000 = 1 poin
    |
    */
    'rupiah_per_point' => env('LOYALTY_RUPIAH_PER_POINT', 5000),
];

