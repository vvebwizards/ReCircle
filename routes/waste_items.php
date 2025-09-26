<?php

use App\Http\Controllers\GeneratorWasteItemController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::get('/waste-items', [GeneratorWasteItemController::class, 'index'])
        ->name('generator.waste-items.index');
    Route::get('/waste-items/create', [GeneratorWasteItemController::class, 'create'])
        ->name('generator.waste-items.create');
    Route::post('/waste-items', [GeneratorWasteItemController::class, 'store'])
        ->name('generator.waste-items.store');
});
