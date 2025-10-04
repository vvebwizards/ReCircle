<?php
// routes/forum.php

use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['jwt.auth'])->group(function () {
    // Forum main pages
    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::get('/forum/c/{category}', [ForumController::class, 'category'])->name('forum.category');
    Route::get('/forum/c/{category}/{discussion}', [ForumController::class, 'show'])->name('forum.discussion');

    // Discussions
    Route::get('/forum/create', [DiscussionController::class, 'create'])->name('forum.discussions.create');
    Route::post('/forum/discussions', [DiscussionController::class, 'store'])->name('forum.discussions.store');

    // Replies
    Route::post('/forum/discussions/{discussion}/replies', [ReplyController::class, 'store'])->name('forum.replies.store');
    Route::post('/forum/replies/{reply}/mark-answer', [ReplyController::class, 'markAsAnswer'])->name('forum.replies.mark-answer');

    // Voting
    Route::post('/forum/vote', [VoteController::class, 'vote'])->name('forum.vote');
});