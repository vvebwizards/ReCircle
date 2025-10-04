<?php
// app/Models/ForumReply.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ForumReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'discussion_id',
        'parent_id',
        'depth',
        'like_count',
        'is_answer',
    ];

    protected $casts = [
        'is_answer' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function discussion(): BelongsTo
    {
        return $this->belongsTo(ForumDiscussion::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ForumReply::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ForumReply::class, 'parent_id')->orderBy('created_at');
    }

    public function votes(): MorphMany
    {
        return $this->morphMany(ForumVote::class, 'votable');
    }

    // Scopes
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeAnswers($query)
    {
        return $query->where('is_answer', true);
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

    // Check if reply has nested replies
    public function hasReplies()
    {
        return $this->replies()->count() > 0;
    }
}