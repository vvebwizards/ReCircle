<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Material extends Model
{
    protected $fillable = ['name'];

    /**
     * Many-to-many: this material belongs to many waste items.
     *
     * @return BelongsToMany<WasteItem, static>
     */
    public function wasteItems(): BelongsToMany
    {
        /** @phpstan-ignore-next-line return.type */
        return $this->belongsToMany(WasteItem::class)
            ->withTimestamps();
    }
}
