<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\Game\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_create_and_list_achievements_for_a_game(): void
    {
        $game = Game::factory()->create();
        $other = Game::factory()->create();
        Achievement::factory()->create([
            'game_id' => $other->id,
            'name' => 'Other',
            'steam_api_name' => 'ACH_OTHER',
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/'.$game->id.'/achievements', [
                'name' => 'First Viking',
                'description' => 'Defeat the first boss.',
                'steam_api_name' => 'ACH_FIRST_BOSS',
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'game_achievements')
            ->assertJsonPath('data.attributes.game_id', $game->id)
            ->assertJsonPath('data.attributes.steam_api_name', 'ACH_FIRST_BOSS');

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/games/'.$game->id.'/achievements')
            ->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.attributes.name', 'First Viking');
    }

    public function test_steam_api_name_is_unique_per_game(): void
    {
        $game = Game::factory()->create();
        $other = Game::factory()->create();
        Achievement::factory()->create([
            'game_id' => $game->id,
            'steam_api_name' => 'ACH_WIN',
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/'.$game->id.'/achievements', [
                'name' => 'Duplicate',
                'steam_api_name' => 'ACH_WIN',
            ])
            ->assertUnprocessable();

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/'.$other->id.'/achievements', [
                'name' => 'Same api name, other game',
                'steam_api_name' => 'ACH_WIN',
            ])
            ->assertCreated();
    }

    public function test_user_can_update_and_delete_achievement_only_inside_its_game(): void
    {
        $game = Game::factory()->create();
        $other = Game::factory()->create();
        $achievement = Achievement::factory()->create([
            'game_id' => $game->id,
            'name' => 'Explorer',
            'steam_api_name' => 'ACH_EXPLORE',
        ]);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/games/'.$other->id.'/achievements/'.$achievement->id)
            ->assertNotFound();

        $this->actingAs($this->user, 'api')
            ->patchJson('/api/v1/games/'.$game->id.'/achievements/'.$achievement->id, [
                'name' => 'World Explorer',
            ])
            ->assertOk()
            ->assertJsonPath('data.attributes.name', 'World Explorer')
            ->assertJsonPath('data.attributes.game_id', $game->id);

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/v1/games/'.$game->id.'/achievements/'.$achievement->id)
            ->assertOk();

        $this->assertDatabaseMissing('game_achievements', ['id' => $achievement->id]);
    }
}
