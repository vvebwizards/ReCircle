<?php
// routes/messages.php

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['jwt.auth'])->group(function () {
    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}', [MessageController::class, 'store'])->name('messages.store');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    
    // Message actions
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead'])->name('messages.mark-as-read');
    Route::get('/messages/check/new', [MessageController::class, 'checkNewMessages'])->name('messages.check-new');
});