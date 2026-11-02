<?php

declare(strict_types=1);

namespace App\Containers\GameSection\PlayerAchievement\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\PlayerAchievement\Models\PlayerAchievement;
use App\Containers\GameSection\SteamUser\Models\SteamUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportPlayerAchievementsFromSteamTest extends TestCase
{
    use RefreshDatabase;

    private const STEAM_ID = '76561197997038451';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        SteamUser::factory()->create([
            'user_id' => $this->user->id,
            'steam_id' => self::STEAM_ID,
            'persona_name' => 'c0ntr_all',
        ]);
        config()->set('steam_web_api.key', 'test-steam-key');
        config()->set('steam_web_api.base_url', 'https://api.steampowered.com');
        config()->set('steam_web_api.language', 'russian');
        config()->set('steam_web_api.retries', 1);
        config()->set('steam_web_api.retry_sleep_ms', 0);
    }

    public function test_imports_player_achievements_when_catalog_exists(): void
    {
        $game = Game::factory()->create(['steam_id' => 3010850, 'name' => 'Gears of War: E-Day']);
        Achievement::factory()->create([
            'game_id' => $game->id,
            'steam_api_name' => 'Spend_Energy',
            'name' => 'Catalog name',
        ]);

        Http::fake([
            'https://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v0001/*' => Http::response($this->playerPayload(), 200),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/'.$game->id.'/player-achievements/import');

        $response->assertOk()
            ->assertJsonPath('data.0.type', 'game_player_achievements')
            ->assertJsonPath('data.0.attributes.achieved', true)
            ->assertJsonPath('meta.created', 1)
            ->assertJsonPath('meta.skipped', 0)
            ->assertJsonPath('meta.catalog_imported', false)
            ->assertJsonPath('meta.game_name', 'Gears of War: E-Day');

        $this->assertDatabaseHas('game_player_achievements', [
            'user_id' => $this->user->id,
            'achieved' => true,
        ]);

        Http::assertSent(function (Request $request): bool {
            return str_contains($request->url(), 'appid=3010850')
                && str_contains($request->url(), 'steamid='.self::STEAM_ID)
                && str_contains($request->url(), 'l=russian');
        });

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/games/'.$game->id.'/player-achievements')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.achieved', true);
    }

    public function test_auto_imports_catalog_when_empty(): void
    {
        $game = Game::factory()->create(['steam_id' => 3010850]);

        Http::fake([
            'https://api.steampowered.com/IPlayerService/GetGameAchievements/v1/*' => Http::response($this->catalogPayload(), 200),
            'https://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v0001/*' => Http::response($this->playerPayload(), 200),
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/'.$game->id.'/player-achievements/import')
            ->assertOk()
            ->assertJsonPath('meta.catalog_imported', true)
            ->assertJsonPath('meta.created', 1)
            ->assertJsonPath('meta.skipped', 0);

        $this->assertDatabaseHas('game_achievements', [
            'game_id' => $game->id,
            'steam_api_name' => 'Spend_Energy',
        ]);
        $this->assertSame(1, PlayerAchievement::query()->count());
    }

    public function test_skips_unknown_apiname_without_failing(): void
    {
        $game = Game::factory()->create(['steam_id' => 3010850]);
        Achievement::factory()->create([
            'game_id' => $game->id,
            'steam_api_name' => 'Other_Achievement',
        ]);

        Http::fake([
            'https://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v0001/*' => Http::response($this->playerPayload(), 200),
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/games/'.$game->id.'/player-achievements/import')
            ->assertOk()
            ->assertJsonPath('meta.created', 0)
            ->assertJsonPath('meta.skipped', 1)
            ->assertJsonPath('meta.total', 0);

        $this->assertDatabaseCount('game_player_achievements', 0);
    }

    public function test_requires_linked_steam_user(): void
    {
        $other = User::factory()->create();
        $game = Game::factory()->create(['steam_id' => 3010850]);

        $this->actingAs($other, 'api')
            ->postJson('/api/v1/games/'.$game->id.'/player-achievements/import')
            ->assertStatus(422)
            ->assertJsonPath('message', 'User '.$other->id.' has no linked Steam profile.');
    }

    public function test_cli_imports_player_achievements(): void
    {
        $game = Game::factory()->create(['steam_id' => 3010850]);
        Achievement::factory()->create([
            'game_id' => $game->id,
            'steam_api_name' => 'Spend_Energy',
        ]);

        Http::fake([
            'https://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v0001/*' => Http::response($this->playerPayload(), 200),
        ]);

        $exitCode = Artisan::call('game:import-player-achievements', [
            'user' => $this->user->id,
            'game' => $game->id,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseHas('game_player_achievements', [
            'user_id' => $this->user->id,
            'achieved' => true,
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
                    'localized_desc' => 'Потратьте 150 000 энергии.',
                    'icon' => 'icon.jpg',
                    'icon_gray' => 'icon_gray.jpg',
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

    /**
     * @return array{playerstats: array<string, mixed>}
     */
    private function playerPayload(): array
    {
        return [
            'playerstats' => [
                'steamID' => self::STEAM_ID,
                'gameName' => 'Gears of War: E-Day',
                'achievements' => [[
                    'apiname' => 'Spend_Energy',
                    'achieved' => 1,
                    'unlocktime' => 1792241916,
                    'name' => 'Стопка за стопкой, за стопкой',
                    'description' => 'Потратьте 150 000 энергии.',
                ]],
                'success' => true,
            ],
        ];
    }
}
