<?php
// app/Models/ForumDiscussion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ForumDiscussion extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'user_id',
        'category_id',
        'is_pinned',
        'is_locked',
        'view_count',
        'reply_count',
        'last_reply_at',
        'last_reply_by',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'last_reply_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'category_id'); // Specify the foreign key
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ForumReply::class, 'discussion_id'); // Specify the foreign key
    }

    public function votes(): MorphMany
    {
        return $this->morphMany(ForumVote::class, 'votable');
    }

    public function lastReplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_reply_by');
    }

    // Route key name
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Scopes
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_locked', false);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('last_reply_at', 'desc');
    }

    // Helper methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function updateReplyCount()
    {
        $this->update([
            'reply_count' => $this->replies()->count(),
            'last_reply_at' => $this->replies()->latest()->first()?->created_at ?? $this->created_at,
            'last_reply_by' => $this->replies()->latest()->first()?->user_id,
        ]);
    }

    // Vote methods
    public function upVotes()
    {
        return $this->votes()->where('type', 'up')->count();
    }

    public function downVotes()
    {
        return $this->votes()->where('type', 'down')->count();
    }

    public function voteScore()
    {
        return $this->upVotes() - $this->downVotes();
    }

    public function userVote(User $user)
    {
        return $this->votes()->where('user_id', $user->id)->first();
    }
}