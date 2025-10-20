<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            // Common waste materials
            'plastic',
            'wood',
            'metal',
            'glass',
            'paper',
            'cardboard',
            'textile',
            'electronic',
            'battery',
            'furniture',

            // Plastic subtypes
            'pvc',
            'pet',
            'hdpe',
            'ldpe',
            'pp',
            'ps',

            // Wood subtypes
            'plywood',
            'mdf',
            'hardwood',
            'softwood',
            'bamboo',

            // Metal subtypes
            'aluminum',
            'steel',
            'copper',
            'iron',
            'brass',

            // Electronic subtypes
            'computer',
            'phone',
            'battery',
            'circuit-board',
            'cable',
        ];

        foreach ($tags as $tagName) {
            Tag::findOrCreateByName($tagName);
        }
    }
}
