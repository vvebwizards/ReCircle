<?php

use App\Http\Controllers\MaterialController;
use Illuminate\Support\Facades\Route;

Route::get('/materials/create', [MaterialController::class, 'create'])->name('materials.create')->middleware('jwt.auth');;
Route::post('/materials', [MaterialController::class, 'store'])
    ->name('materials.store')
    ->middleware('jwt.auth');

