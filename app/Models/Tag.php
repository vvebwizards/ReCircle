<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'display_name',
    ];

    /**
     * Get all waste items that are assigned this tag.
     */
    public function wasteItems(): MorphToMany
    {
        return $this->morphedByMany(WasteItem::class, 'taggable')
            ->withPivot('confidence', 'is_auto_generated')
            ->withTimestamps();
    }

    /**
     * Get a normalized version of the tag name for storage
     */
    public static function normalizeName(string $name): string
    {
        return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
    }

    /**
     * Create a new tag or get an existing one by name
     */
    public static function findOrCreateByName(string $name): self
    {
        $normalizedName = self::normalizeName($name);
        $displayName = trim($name, '# ');

        return self::firstOrCreate(
            ['name' => $normalizedName],
            ['display_name' => $displayName]
        );
    }
}
