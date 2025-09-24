<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $fillable = ['name'];

    /**
     * @return HasMany<WasteItem, static>
     */
    public function wasteItems(): HasMany
    {
        /** @phpstan-ignore-next-line return.type */
        return $this->hasMany(WasteItem::class);
    }
}
