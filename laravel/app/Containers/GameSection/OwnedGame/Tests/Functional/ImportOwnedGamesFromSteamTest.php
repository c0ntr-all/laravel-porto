<?php

declare(strict_types=1);

namespace App\Containers\GameSection\OwnedGame\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\OwnedGame\Models\OwnedGame;
use App\Containers\GameSection\SteamUser\Models\SteamUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportOwnedGamesFromSteamTest extends TestCase
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
        config()->set('steam_web_api.retries', 1);
        config()->set('steam_web_api.retry_sleep_ms', 0);
    }

    public function test_imports_owned_games_matching_local_catalog(): void
    {
        $game = Game::factory()->create(['steam_id' => 220, 'name' => 'Half-Life 2']);

        Http::fake([
            'https://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/*' => Http::response($this->ownedGamesPayload(), 200),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/owned-games/import');

        $response->assertOk()
            ->assertJsonPath('data.0.type', 'game_owned_games')
            ->assertJsonPath('data.0.attributes.playtime_forever', 643)
            ->assertJsonPath('data.0.attributes.game_id', $game->id)
            ->assertJsonPath('meta.created', 1)
            ->assertJsonPath('meta.skipped', 1)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.game_count', 523);

        $this->assertDatabaseHas('game_owned_games', [
            'user_id' => $this->user->id,
            'game_id' => $game->id,
            'playtime_forever' => 643,
        ]);

        Http::assertSent(function (Request $request): bool {
            return str_contains($request->url(), 'IPlayerService/GetOwnedGames/v0001')
                && str_contains($request->url(), 'steamid='.self::STEAM_ID)
                && str_contains($request->url(), 'key=test-steam-key');
        });

        $list = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/owned-games');

        $list->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.playtime_forever', 643);

        $includedGame = collect($list->json('included'))->firstWhere('type', 'games');
        $this->assertIsArray($includedGame);
        $this->assertSame(220, $includedGame['attributes']['steam_id']);
    }

    public function test_reimports_update_playtime(): void
    {
        $game = Game::factory()->create(['steam_id' => 220]);
        OwnedGame::factory()->create([
            'user_id' => $this->user->id,
            'game_id' => $game->id,
            'playtime_forever' => 10,
        ]);

        Http::fake([
            'https://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/*' => Http::response($this->ownedGamesPayload(), 200),
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/owned-games/import')
            ->assertOk()
            ->assertJsonPath('meta.created', 0)
            ->assertJsonPath('meta.updated', 1)
            ->assertJsonPath('meta.skipped', 1);

        $this->assertDatabaseHas('game_owned_games', [
            'user_id' => $this->user->id,
            'game_id' => $game->id,
            'playtime_forever' => 643,
        ]);
    }

    public function test_requires_linked_steam_user(): void
    {
        $other = User::factory()->create();

        $this->actingAs($other, 'api')
            ->postJson('/api/v1/owned-games/import')
            ->assertStatus(422)
            ->assertJsonPath('message', 'User '.$other->id.' has no linked Steam profile.');
    }

    public function test_cli_imports_owned_games(): void
    {
        $game = Game::factory()->create(['steam_id' => 220]);

        Http::fake([
            'https://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/*' => Http::response($this->ownedGamesPayload(), 200),
        ]);

        $exitCode = Artisan::call('game:import-owned-games', [
            'user' => $this->user->id,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseHas('game_owned_games', [
            'user_id' => $this->user->id,
            'game_id' => $game->id,
            'playtime_forever' => 643,
        ]);
    }

    /**
     * @return array{response: array{game_count: int, games: list<array<string, mixed>>}}
     */
    private function ownedGamesPayload(): array
    {
        return [
            'response' => [
                'game_count' => 523,
                'games' => [
                    [
                        'appid' => 220,
                        'playtime_forever' => 643,
                        'playtime_windows_forever' => 0,
                        'playtime_mac_forever' => 0,
                        'playtime_linux_forever' => 0,
                        'playtime_deck_forever' => 0,
                        'rtime_last_played' => 1550414819,
                        'playtime_disconnected' => 0,
                    ],
                    [
                        'appid' => 570,
                        'playtime_forever' => 120,
                        'rtime_last_played' => 0,
                    ],
                ],
            ],
        ];
    }
}
