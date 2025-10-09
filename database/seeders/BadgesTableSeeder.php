<?php

// database/seeders/BadgesTableSeeder.php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgesTableSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            // Bronze Badges
            [
                'name' => 'First Discussion',
                'description' => 'Started your first discussion in the community',
                'icon' => 'fa-comment-medical',
                'color' => '#CD7F32',
                'type' => 'bronze',
                'criteria' => 'first_discussion',
                'threshold' => 1,
                'points' => 50,
            ],
            [
                'name' => 'Helpful Reply',
                'description' => 'Posted your first helpful reply',
                'icon' => 'fa-reply',
                'color' => '#CD7F32',
                'type' => 'bronze',
                'criteria' => 'reply_count',
                'threshold' => 1,
                'points' => 25,
            ],
            [
                'name' => 'Problem Solver',
                'description' => 'Provided your first solution',
                'icon' => 'fa-lightbulb',
                'color' => '#CD7F32',
                'type' => 'bronze',
                'criteria' => 'solution_count',
                'threshold' => 1,
                'points' => 100,
            ],
            [
                'name' => 'Community Lover',
                'description' => 'Received 10 likes on your content',
                'icon' => 'fa-heart',
                'color' => '#CD7F32',
                'type' => 'bronze',
                'criteria' => 'likes_received',
                'threshold' => 10,
                'points' => 30,
            ],
            [
                'name' => 'Weekly Active',
                'description' => 'Active in the community for 7 days',
                'icon' => 'fa-calendar-check',
                'color' => '#CD7F32',
                'type' => 'bronze',
                'criteria' => 'days_active',
                'threshold' => 7,
                'points' => 75,
            ],

            // Silver Badges
            [
                'name' => 'Discussion Pro',
                'description' => 'Created 25 discussions',
                'icon' => 'fa-comments',
                'color' => '#C0C0C0',
                'type' => 'silver',
                'criteria' => 'discussion_count',
                'threshold' => 25,
                'points' => 250,
            ],
            [
                'name' => 'Helpful Expert',
                'description' => 'Posted 50 helpful replies',
                'icon' => 'fa-comment-dots',
                'color' => '#C0C0C0',
                'type' => 'silver',
                'criteria' => 'reply_count',
                'threshold' => 50,
                'points' => 200,
            ],
            [
                'name' => 'Solution Master',
                'description' => 'Provided 10 solutions',
                'icon' => 'fa-trophy',
                'color' => '#C0C0C0',
                'type' => 'silver',
                'criteria' => 'solution_count',
                'threshold' => 10,
                'points' => 500,
            ],
            [
                'name' => 'Community Favorite',
                'description' => 'Received 100 likes',
                'icon' => 'fa-star',
                'color' => '#C0C0C0',
                'type' => 'silver',
                'criteria' => 'likes_received',
                'threshold' => 100,
                'points' => 150,
            ],
            [
                'name' => 'Monthly Active',
                'description' => 'Active in the community for 30 days',
                'icon' => 'fa-calendar-alt',
                'color' => '#C0C0C0',
                'type' => 'silver',
                'criteria' => 'days_active',
                'threshold' => 30,
                'points' => 300,
            ],

            // Gold Badges
            [
                'name' => 'Discussion Champion',
                'description' => 'Created 100 discussions',
                'icon' => 'fa-crown',
                'color' => '#FFD700',
                'type' => 'gold',
                'criteria' => 'discussion_count',
                'threshold' => 100,
                'points' => 1000,
            ],
            [
                'name' => 'Community Mentor',
                'description' => 'Posted 250 helpful replies',
                'icon' => 'fa-user-graduate',
                'color' => '#FFD700',
                'type' => 'gold',
                'criteria' => 'reply_count',
                'threshold' => 250,
                'points' => 750,
            ],
            [
                'name' => 'Ultimate Solver',
                'description' => 'Provided 25 solutions',
                'icon' => 'fa-puzzle-piece',
                'color' => '#FFD700',
                'type' => 'gold',
                'criteria' => 'solution_count',
                'threshold' => 25,
                'points' => 1250,
            ],
            [
                'name' => 'Community Star',
                'description' => 'Received 500 likes',
                'icon' => 'fa-gem',
                'color' => '#FFD700',
                'type' => 'gold',
                'criteria' => 'likes_received',
                'threshold' => 500,
                'points' => 500,
            ],
            [
                'name' => 'Yearly Active',
                'description' => 'Active in the community for 365 days',
                'icon' => 'fa-award',
                'color' => '#FFD700',
                'type' => 'gold',
                'criteria' => 'days_active',
                'threshold' => 365,
                'points' => 1500,
            ],

            // Platinum Badges
            [
                'name' => 'Forum Legend',
                'description' => 'Reached 5000 community points',
                'icon' => 'fa-fire',
                'color' => '#E5E4E2',
                'type' => 'platinum',
                'criteria' => 'total_points',
                'threshold' => 5000,
                'points' => 0,
            ],
            [
                'name' => 'Streak Master',
                'description' => 'Maintained a 30-day activity streak',
                'icon' => 'fa-bolt',
                'color' => '#E5E4E2',
                'type' => 'platinum',
                'criteria' => 'streak',
                'threshold' => 30,
                'points' => 1000,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::create($badge);
        }
    }
}
