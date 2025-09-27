<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminWasteItemController;

// Routes under /admin prefix (grouped in web.php)
Route::prefix('listings')->name('admin.listings.')->group(function () {
    Route::get('/', [AdminWasteItemController::class, 'index'])->name('index'); // Blade page + initial data
    Route::get('/{wasteItem}', [AdminWasteItemController::class, 'show'])->name('show'); // JSON detail
    Route::match(['put','patch'],'/{wasteItem}', [AdminWasteItemController::class, 'update'])->name('update');
    Route::delete('/{wasteItem}', [AdminWasteItemController::class, 'destroy'])->name('destroy');
});
