<?php

use App\Http\Controllers\AdminCartController;
use Illuminate\Support\Facades\Route;

Route::get('/carts', [AdminCartController::class, 'index'])->name('admin.carts.index');
Route::get('/carts/{cart}', [AdminCartController::class, 'show'])->name('admin.carts.show');
Route::put('/carts/{cart}', [AdminCartController::class, 'update'])->name('admin.carts.update');
Route::delete('/carts/{cart}', [AdminCartController::class, 'destroy'])->name('admin.carts.destroy');
