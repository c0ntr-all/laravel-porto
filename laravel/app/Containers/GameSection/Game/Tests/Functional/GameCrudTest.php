<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\Game\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_list_games(): void
    {
        $this->getJson('/api/v1/games')->assertUnauthorized();
    }

    public function test_user_can_create_game(): void
    {
        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games', [
                'name' => 'Valheim',
                'description' => 'Survival.',
                'img' => 'https://example.com/header.jpg',
                'air_date' => '2021-02-02',
                'developer' => 'Iron Gate AB',
                'publisher' => 'Coffee Stain Publishing',
                'steam_id' => 892970,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'games')
            ->assertJsonPath('data.attributes.name', 'Valheim')
            ->assertJsonPath('data.attributes.air_date', '2021-02-02')
            ->assertJsonPath('data.attributes.steam_id', 892970);

        $this->assertDatabaseHas('games', [
            'name' => 'Valheim',
            'steam_id' => 892970,
            'developer' => 'Iron Gate AB',
        ]);
    }

    public function test_user_cannot_create_duplicate_steam_id(): void
    {
        Game::factory()->create(['steam_id' => 892970]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games', [
                'name' => 'Copy',
                'steam_id' => 892970,
            ])
            ->assertUnprocessable();
    }

    public function test_user_can_list_get_update_and_delete_game(): void
    {
        $game = Game::factory()->create(['name' => 'Hades', 'steam_id' => 1145360]);
        $achievement = Achievement::factory()->create(['game_id' => $game->id]);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/games')
            ->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.attributes.name', 'Hades');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/games/'.$game->id)
            ->assertOk()
            ->assertJsonPath('data.attributes.steam_id', 1145360);

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/games/'.$game->id, [
                'name' => 'Hades II',
                'publisher' => 'Supergiant Games',
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'Hades II')
            ->assertJsonPath('data.attributes.publisher', 'Supergiant Games');

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/games/'.$game->id)
            ->assertOk();

        $this->assertDatabaseMissing('games', ['id' => $game->id]);
        $this->assertDatabaseMissing('game_achievements', ['id' => $achievement->id]);
    }
}
