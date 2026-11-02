<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\Game\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportGameAchievementsFromSteamTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        config()->set('steam_web_api.key', 'test-steam-key');
        config()->set('steam_web_api.base_url', 'https://api.steampowered.com');
        config()->set('steam_web_api.language', 'russian');
        config()->set('steam_web_api.retries', 1);
        config()->set('steam_web_api.retry_sleep_ms', 0);
    }

    public function test_imports_game_achievement_catalog_from_steam(): void
    {
        $game = Game::factory()->create(['steam_id' => 3010850]);

        Http::fake([
            'https://api.steampowered.com/IPlayerService/GetGameAchievements/v1/*' => Http::response($this->catalogPayload(), 200),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/'.$game->id.'/achievements/import');

        $response->assertOk()
            ->assertJsonPath('data.0.type', 'game_achievements')
            ->assertJsonPath('data.0.attributes.steam_api_name', 'Spend_Energy')
            ->assertJsonPath('data.0.attributes.name', 'Стопка за стопкой, за стопкой')
            ->assertJsonPath('data.0.attributes.player_percent_unlocked', '14.4')
            ->assertJsonPath('data.0.attributes.steam_internal_key', 4864)
            ->assertJsonPath('data.0.attributes.max_progress', 150000)
            ->assertJsonPath('meta.created', 1)
            ->assertJsonPath('meta.updated', 0)
            ->assertJsonPath('meta.total', 1);

        $this->assertDatabaseHas('game_achievements', [
            'game_id' => $game->id,
            'steam_api_name' => 'Spend_Energy',
            'hidden' => false,
            'progress_type' => 1,
        ]);

        Http::assertSent(function (Request $request): bool {
            return str_contains($request->url(), 'appid=3010850')
                && str_contains($request->url(), 'language=russian')
                && str_contains($request->url(), 'key=test-steam-key');
        });
    }

    public function test_reimport_updates_existing_catalog_rows(): void
    {
        $game = Game::factory()->create(['steam_id' => 3010850]);
        Achievement::factory()->create([
            'game_id' => $game->id,
            'steam_api_name' => 'Spend_Energy',
            'name' => 'Old name',
        ]);

        Http::fake([
            'https://api.steampowered.com/IPlayerService/GetGameAchievements/v1/*' => Http::response($this->catalogPayload(), 200),
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/'.$game->id.'/achievements/import')
            ->assertOk()
            ->assertJsonPath('meta.created', 0)
            ->assertJsonPath('meta.updated', 1)
            ->assertJsonPath('data.0.attributes.name', 'Стопка за стопкой, за стопкой');

        $this->assertSame(1, Achievement::query()->where('game_id', $game->id)->count());
    }

    public function test_game_without_steam_id_cannot_import_catalog(): void
    {
        $game = Game::factory()->create(['steam_id' => null]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/'.$game->id.'/achievements/import')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Game '.$game->id.' has no steam_id and cannot import Steam achievements.');
    }

    public function test_cli_imports_game_achievements(): void
    {
        $game = Game::factory()->create(['steam_id' => 3010850]);

        Http::fake([
            'https://api.steampowered.com/IPlayerService/GetGameAchievements/v1/*' => Http::response($this->catalogPayload(), 200),
        ]);

        $exitCode = Artisan::call('game:import-achievements', ['game' => $game->id]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseHas('game_achievements', [
            'game_id' => $game->id,
            'steam_api_name' => 'Spend_Energy',
        ]);
    }

    /**
     * @return array{response: array{achievements: list<array<string, mixed>>}}
     */
    private function catalogPayload(): array
    {
        return [
            'response' => [
                'achievements' => [[
                    'internal_name' => 'Spend_Energy',
                    'localized_name' => 'Стопка за стопкой, за стопкой',
                    'localized_desc' => 'Потратьте 150 000 энергии в пункте снабжения в режиме «Осада Орды» (на любом уровне сложности).',
                    'icon' => 'ff9f928e37583a0eb0d02095756f2b5508aa0499.jpg',
                    'icon_gray' => 'ff9f928e37583a0eb0d02095756f2b5508aa0499.jpg',
                    'hidden' => false,
                    'player_percent_unlocked' => '14.4',
                    'internal_key' => 4864,
                    'min_progress_int' => 0,
                    'max_progress_int' => 150000,
                    'groupid' => 0,
                    'archived' => false,
                    'progress_type' => 1,
                ]],
            ],
        ];
    }
}
