<?php

use App\Http\Controllers\MarketplaceController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
    Route::get('/marketplace/{wasteItem}', [MarketplaceController::class, 'show'])->name('marketplace.show');
});
