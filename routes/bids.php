<?php

use App\Http\Controllers\BidController;
use Illuminate\Support\Facades\Route;

Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/waste-items/{wasteItem}/bids', [BidController::class, 'index'])->name('bids.index');
    Route::post('/waste-items/{wasteItem}/bids', [BidController::class, 'store'])->name('bids.store');

    Route::get('/bids/{bid}', [BidController::class, 'show'])->name('bids.show');
    Route::patch('/bids/{bid}', [BidController::class, 'update'])->name('bids.update');
    Route::patch('/bids/{bid}/status', [BidController::class, 'updateStatus'])->name('bids.updateStatus');
    Route::patch('/bids/{bid}/withdraw', [BidController::class, 'withdraw'])->name('bids.withdraw');

    //pickup routes
    Route::post('/bids/{bid}/accept', [BidController::class, 'accept'])
    ->name('bids.accept');
});
