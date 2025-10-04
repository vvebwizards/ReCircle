<?php
// app/Models/ForumCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ForumCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function discussions(): HasMany
    {
        return $this->hasMany(ForumDiscussion::class, 'category_id');
    }

    // Add the latestDiscussion relationship
    public function latestDiscussion(): HasOne
    {
        return $this->hasOne(ForumDiscussion::class, 'category_id')->latest();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Scope for active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Get discussions count with eager loading
    public function getDiscussionsCountAttribute()
    {
        return $this->discussions()->count();
    }

    // Remove the old latestDiscussion attribute and use the relationship instead
}