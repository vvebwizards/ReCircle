<?php

use App\Http\Controllers\MaterialController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {

    Route::get('/materials/create', [MaterialController::class, 'create'])
        ->name('materials.create');

    Route::post('/materials/store', [MaterialController::class, 'store'])
        ->name('materials.store');

    Route::get('/materials', [MaterialController::class, 'index'])
        ->name('maker.materials.index');

    Route::get('/materials/{material}/images', [MaterialController::class, 'getMaterialImages'])
        ->name('materials.images');

     Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])
        ->name('maker.materials.destroy');

   Route::put('/materials/{material}', [MaterialController::class, 'destroy'])
        ->name('maker.materials.destroy');

    Route::get('/materials/{material}/edit', [MaterialController::class, 'edit'])
        ->name('maker.materials.edit');

    Route::put('/materials/{material}', [MaterialController::class, 'update'])
        ->name('maker.materials.update');

});
