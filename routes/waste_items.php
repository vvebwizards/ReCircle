<?php

use App\Http\Controllers\GeneratorWasteItemController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::get('/waste-items', [GeneratorWasteItemController::class, 'index'])
        ->name('generator.waste-items.index');
});
