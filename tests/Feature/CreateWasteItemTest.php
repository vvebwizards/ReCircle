<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateWasteItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_waste_item(): void
    {
        $user = User::factory()->create();

        $payload = [
            'title' => 'Pile of wooden pallets',
            'images' => ['img1.jpg', 'img2.jpg'],
            'estimated_weight' => 123.45,
            'condition' => 'good',
            'location' => ['lat' => 12.34, 'lng' => 56.78],
            'notes' => 'Stored outside under cover',
        ];

        $token = app(\App\Services\JwtService::class)->issue($user)['token'];

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/waste-items', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.title', $payload['title'])
            ->assertJsonPath('data.generator_id', $user->id);

        $this->assertDatabaseHas('waste_items', [
            'title' => $payload['title'],
            'generator_id' => $user->id,
        ]);
    }
}
