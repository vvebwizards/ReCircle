<?php

use App\Http\Controllers\ReclamationController;
use App\Http\Controllers\ReclamationResponseController;
use Illuminate\Support\Facades\Route;

// Public routes (authenticated users)
Route::middleware(['jwt.auth'])->group(function () {

    // Reclamations
    Route::get('/reclamations', [ReclamationController::class, 'index'])
        ->name('reclamations.index');

    Route::get('/reclamations/create', [ReclamationController::class, 'create'])
        ->name('reclamations.create');

    Route::post('/reclamations', [ReclamationController::class, 'store'])
        ->name('reclamations.store');

    Route::get('/reclamations/{reclamation}', [ReclamationController::class, 'show'])
        ->name('reclamations.show');

    Route::get('/reclamations/{reclamation}/edit', [ReclamationController::class, 'edit'])
        ->name('reclamations.edit');

    Route::put('/reclamations/{reclamation}', [ReclamationController::class, 'update'])
        ->name('reclamations.update');

    Route::delete('/reclamations/{reclamation}', [ReclamationController::class, 'destroy'])
        ->name('reclamations.destroy');

    // Add this to the authenticated user routes section
    Route::post('/reclamations/{reclamation}/user-reply', [ReclamationController::class, 'storeUserReply'])
        ->name('reclamation.user-reply.store');
});

// Admin routes
Route::middleware(['jwt.auth'])->group(function () {

    // Update reclamation status
    Route::patch('/reclamations/{reclamation}/status', [ReclamationController::class, 'updateStatus'])
        ->name('reclamations.update-status');

    // Reclamation Responses
    Route::get('/admin/reclamation-responses', [ReclamationResponseController::class, 'index'])
        ->name('reclamation-responses.index');

    Route::post('/reclamations/{reclamation}/responses', [ReclamationResponseController::class, 'store'])
        ->name('reclamation-responses.store');

    Route::get('/reclamation-responses/{response}', [ReclamationResponseController::class, 'show'])
        ->name('reclamation-responses.show');

    Route::get('/reclamation-responses/{response}/edit', [ReclamationResponseController::class, 'edit'])
        ->name('reclamation-responses.edit');

    Route::put('/reclamation-responses/{response}', [ReclamationResponseController::class, 'update'])
        ->name('reclamation-responses.update');

    Route::delete('/reclamation-responses/{response}', [ReclamationResponseController::class, 'destroy'])
        ->name('reclamation-responses.destroy');
});
