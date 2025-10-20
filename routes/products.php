<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductsAdminController;
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

    Route::post('/products/{id}/publish', [ProductController::class, 'publish'])
        ->name('maker.products.publish');

    Route::patch('/products/{product}/stock', [ProductController::class, 'updateStock'])
        ->name('maker.products.update-stock');

    Route::post('/products/pricing-suggestions', [ProductController::class, 'getPricingSuggestions'])
        ->name('maker.products.pricing-suggestions');

});

Route::prefix('admin')->middleware([\App\Http\Middleware\AdminMiddleware::class, 'jwt.auth'])->name('admin.')->group(function () {
    Route::get('/products', [ProductsAdminController::class, 'productsIndex'])
        ->name('products.index');

    Route::get('/products/data', [ProductsAdminController::class, 'productsData'])
        ->name('products.data');

    Route::get('/products/{product}/view', [ProductsAdminController::class, 'productsShow'])
        ->name('products.show');

    Route::get('/products/{product}/edit-form', [ProductsAdminController::class, 'productsEdit'])
        ->name('products.edit-form');

    Route::put('/products/{product}', [ProductsAdminController::class, 'productsUpdate'])
        ->name('products.update');

    Route::delete('/products/{product}/admin-delete', [ProductsAdminController::class, 'productsDestroy'])
        ->name('products.destroy');

    Route::post('/products/{product}/toggle-featured', [ProductsAdminController::class, 'productsToggleFeatured'])
        ->name('products.toggle-featured');
});
