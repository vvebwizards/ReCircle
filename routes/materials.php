<?php

use App\Http\Controllers\Admin\MaterialsAdminController;
use App\Http\Controllers\MaterialController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::get('/materials/create', [MaterialController::class, 'create'])
        ->name('materials.create');

    Route::post('/materials/store', [MaterialController::class, 'store'])
        ->name('materials.store');

    Route::get('/materials', [MaterialController::class, 'index'])
        ->name('maker.materials.index');

    Route::get('/materials/{material}', [MaterialController::class, 'show'])
        ->name('maker.materials.show');

    Route::get('/materials/{material}/images', [MaterialController::class, 'getMaterialImages'])
        ->name('materials.images');

    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])
        ->name('maker.materials.destroy');

    Route::get('/materials/{material}/edit', [MaterialController::class, 'edit'])
        ->name('maker.materials.edit');

    Route::put('/materials/{material}', [MaterialController::class, 'update'])
        ->name('maker.materials.update');

    Route::prefix('admin')->middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::get('/materials', [MaterialsAdminController::class, 'materialsIndex'])
            ->name('admin.materials.index');

        Route::get('/materials/data', [MaterialsAdminController::class, 'materialsData'])
            ->name('admin.materials.data');

        Route::get('/materials/{material}/view', [MaterialsAdminController::class, 'materialsShow'])
            ->name('admin.materials.show');

        Route::get('/materials/{material}/edit-form', [MaterialsAdminController::class, 'materialsEdit'])
            ->name('admin.materials.edit-form');

        Route::put('/materials/{material}/admin-update', [MaterialsAdminController::class, 'materialsUpdate'])
            ->name('admin.materials.update');

        Route::delete('/materials/{material}/admin-delete', [MaterialsAdminController::class, 'materialsDestroy'])
            ->name('admin.materials.destroy');
    });
});
