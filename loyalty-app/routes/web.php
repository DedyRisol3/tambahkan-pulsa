<?php

use App\Http\Controllers\LoyaltyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('Auth/Login');
});

Route::get('/loyalty/sso/callback', function () {
    return Inertia::render('Auth/SsoCallback');
})->name('loyalty.sso.callback');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
});

// API Routes untuk Penukaran Pulsa
Route::prefix('api/pulsa')->group(function () {
    // Mendapatkan daftar paket pulsa
    Route::get('/packages', [LoyaltyController::class, 'getPulsaPackages']);
    
    // Proses penukaran poin ke pulsa
    Route::post('/redeem', [LoyaltyController::class, 'redeemPulsa']);
    
    // Riwayat penukaran pulsa user
    Route::get('/history/{userId}', [LoyaltyController::class, 'getPulsaHistory']);
    
    // Admin routes
    Route::get('/admin/all', [LoyaltyController::class, 'getAllPulsaRedemptions']);
    Route::put('/admin/{id}/status', [LoyaltyController::class, 'updatePulsaStatus']);
});
