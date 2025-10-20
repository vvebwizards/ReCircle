<?php

use App\Http\Controllers\BuyerMarketPlaceController;
use App\Http\Controllers\MarketplaceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['jwt.auth', 'role:'.\App\Enums\UserRole::MAKER->value])->group(function () {
    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
    Route::get('/marketplace/{wasteItem}', [MarketplaceController::class, 'show'])->name('marketplace.show');

});

Route::middleware(['jwt.auth', 'role:'.\App\Enums\UserRole::BUYER->value])->group(function () {
    // Buyer dashboard (default path /buyerdashboard)
    Route::get('/buyerdashboard', function () {
        return view('buyer.dashboard');
    })->name('buyer.dashboard');

    Route::get('/buyer/marketplace', [BuyerMarketPlaceController::class, 'index'])->name('buyer.marketplace.index');
    Route::get('/buyer/marketplace/{type}/{id}', [BuyerMarketPlaceController::class, 'show'])->name('buyer.marketplace.show');
});
