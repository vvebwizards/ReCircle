<?php
// database/seeders/ForumCategoriesTableSeeder.php

namespace Database\Seeders;

use App\Models\ForumCategory;
use Illuminate\Database\Seeder;

class ForumCategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'General Discussion',
                'slug' => 'general',
                'description' => 'General discussions about waste transformation and circular economy',
                'color' => '#3B82F6',
                'icon' => 'fa-comments',
                'order' => 1,
            ],
            [
                'name' => 'Technical Questions',
                'slug' => 'technical',
                'description' => 'Ask technical questions about repair, upcycling, and transformation processes',
                'color' => '#10B981',
                'icon' => 'fa-wrench',
                'order' => 2,
            ],
            [
                'name' => 'Material Exchange',
                'slug' => 'materials',
                'description' => 'Discuss available materials, sourcing, and material properties',
                'color' => '#F59E0B',
                'icon' => 'fa-recycle',
                'order' => 3,
            ],
            [
                'name' => 'Project Showcase',
                'slug' => 'projects',
                'description' => 'Showcase your completed waste transformation projects',
                'color' => '#8B5CF6',
                'icon' => 'fa-trophy',
                'order' => 4,
            ],
            [
                'name' => 'Business & Marketplace',
                'slug' => 'business',
                'description' => 'Discuss business opportunities, pricing, and marketplace strategies',
                'color' => '#EF4444',
                'icon' => 'fa-store',
                'order' => 5,
            ],
            [
                'name' => 'Ideas & Innovation',
                'slug' => 'ideas',
                'description' => 'Share innovative ideas and concepts for waste transformation',
                'color' => '#EC4899',
                'icon' => 'fa-lightbulb',
                'order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            ForumCategory::create($category);
        }
    }
}