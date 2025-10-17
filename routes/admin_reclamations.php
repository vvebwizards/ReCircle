<?php

use App\Http\Controllers\Admin\AdminReclamationController;
use Illuminate\Support\Facades\Route;

// Add these routes to your existing admin routes file

Route::middleware(['jwt.auth'])->prefix('admin')->name('admin.')->group(function () {

    // Reclamations Management
    Route::get('/reclamations', [AdminReclamationController::class, 'index'])
        ->name('reclamations.index');

    Route::get('/reclamations/{reclamation}', [AdminReclamationController::class, 'show'])
        ->name('reclamations.show');

    Route::post('/reclamations/{reclamation}/response', [AdminReclamationController::class, 'storeResponse'])
        ->name('reclamations.response.store');

    Route::patch('/reclamations/{reclamation}/status', [AdminReclamationController::class, 'updateStatus'])
        ->name('reclamations.update-status');

    Route::delete('/reclamations/{reclamation}', [AdminReclamationController::class, 'destroy'])
        ->name('reclamations.destroy');

    Route::post('/reclamations/bulk-action', [AdminReclamationController::class, 'bulkAction'])
        ->name('reclamations.bulk-action');

    // API endpoint for pending count badge
    Route::get('/reclamations/pending-count', [AdminReclamationController::class, 'pendingCount'])
        ->name('reclamations.pending-count');

});
