<?php

// routes/ai.php

use App\Http\Controllers\AIController;
use Illuminate\Support\Facades\Route;

Route::middleware(['jwt.auth'])->group(function () {
    Route::prefix('ai')->group(function () {
        Route::post('/suggest-reply', [AIController::class, 'generateReplySuggestions'])->name('ai.suggest-reply');
        Route::post('/suggest-reply-to-reply', [AIController::class, 'generateReplyToReply'])->name('ai.suggest-reply-to-reply');
        Route::post('/generate-summary', [AIController::class, 'generateSummary'])->name('ai.generate-summary');
    });
});
