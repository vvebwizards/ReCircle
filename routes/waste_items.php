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
    Route::get('/waste-items/{wasteItem}', [GeneratorWasteItemController::class, 'show'])
        ->name('generator.waste-items.show');
    Route::put('/waste-items/{wasteItem}', [GeneratorWasteItemController::class, 'update'])
        ->name('generator.waste-items.update');
    Route::delete('/waste-items/{wasteItem}', [GeneratorWasteItemController::class, 'destroy'])
        ->name('generator.waste-items.destroy');
});
