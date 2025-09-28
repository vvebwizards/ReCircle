<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteItemImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'waste_item_id',
        'image_path',
        'order',
    ];

    public function wasteItem()
    {
        return $this->belongsTo(WasteItem::class);
    }

    public function getImageUrlAttribute(): string
    {
        return asset($this->image_path);
    }
}
