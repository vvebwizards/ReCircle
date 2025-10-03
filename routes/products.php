<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {

    Route::get('/products', [ProductController::class, 'index'])
        ->name('maker.products');

    Route::get('/products/create', [ProductController::class, 'create'])
        ->name('maker.products.create');

    Route::post('/products/store', [ProductController::class, 'store'])
        ->name('maker.products.store');

    Route::get('/products/{product}', [ProductController::class, 'show'])
        ->name('maker.products.show');

    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
        ->name('maker.products.edit');

    Route::put('/products/{product}', [ProductController::class, 'update'])
        ->name('maker.products.update');

    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->name('maker.products.destroy');

    Route::post('/products/{product}/publish', [ProductController::class, 'publish'])
        ->name('maker.products.publish');

    Route::patch('/products/{product}/stock', [ProductController::class, 'updateStock'])
        ->name('maker.products.update-stock');

});
