<?php

declare(strict_types=1);

namespace App\Containers\AppSection\SteamUser\Tests\Functional;

use App\Containers\AppSection\SteamUser\Models\SteamUser;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportSteamUserTest extends TestCase
{
    use RefreshDatabase;

    private const STEAM_ID = '76561197997038451';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        config()->set('steam_web_api.key', 'test-steam-key');
        config()->set('steam_web_api.base_url', 'https://api.steampowered.com');
        config()->set('steam_web_api.retries', 1);
        config()->set('steam_web_api.retry_sleep_ms', 0);
    }

    public function test_guest_cannot_import_steam_user(): void
    {
        $this->postJson('/api/v1/steam-user/import', ['steam_id' => self::STEAM_ID])
            ->assertUnauthorized();
    }

    public function test_imports_steam_profile_for_authenticated_user(): void
    {
        Http::fake([
            'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/*' => Http::response($this->payload(), 200),
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/steam-user/import', ['steam_id' => self::STEAM_ID]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'steam_users')
            ->assertJsonPath('data.attributes.user_id', $this->user->id)
            ->assertJsonPath('data.attributes.steam_id', self::STEAM_ID)
            ->assertJsonPath('data.attributes.persona_name', 'c0ntr_all')
            ->assertJsonPath('data.attributes.real_name', 'Серега')
            ->assertJsonPath('data.attributes.community_visibility_state', 3)
            ->assertJsonPath('data.attributes.profile_state', 1)
            ->assertJsonPath('data.attributes.comment_permission', 1)
            ->assertJsonPath('data.attributes.avatar_hash', '8053f456b79059deb9472c0097868240cca7becc')
            ->assertJsonPath('data.attributes.persona_state', 1)
            ->assertJsonPath('data.attributes.primary_clan_id', '103582791433603671')
            ->assertJsonPath('data.attributes.persona_state_flags', 0)
            ->assertJsonPath('data.attributes.loc_country_code', 'RU')
            ->assertJsonPath('data.attributes.loc_state_code', '66')
            ->assertJsonPath('data.attributes.loc_city_id', 42316)
            ->assertJsonPath('meta.was_created', true);

        $this->assertDatabaseHas('steam_users', [
            'user_id' => $this->user->id,
            'steam_id' => self::STEAM_ID,
            'persona_name' => 'c0ntr_all',
            'real_name' => 'Серега',
            'loc_country_code' => 'RU',
            'loc_city_id' => 42316,
        ]);

        Http::assertSent(function (Request $request): bool {
            return str_contains($request->url(), 'key=test-steam-key')
                && str_contains($request->url(), 'steamids='.self::STEAM_ID);
        });

        $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/steam-user')
            ->assertOk()
            ->assertJsonPath('data.attributes.persona_name', 'c0ntr_all');
    }

    public function test_reimport_updates_existing_profile(): void
    {
        Http::fake([
            'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/*' => Http::sequence()
                ->push($this->payload(), 200)
                ->push($this->payload(personaName: 'updated_name'), 200),
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/steam-user/import', ['steam_id' => self::STEAM_ID])
            ->assertCreated();

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/steam-user/import', ['steam_id' => self::STEAM_ID])
            ->assertOk()
            ->assertJsonPath('data.attributes.persona_name', 'updated_name')
            ->assertJsonPath('meta.was_created', false);

        $this->assertSame(1, SteamUser::query()->count());
    }

    public function test_cannot_link_steam_id_already_used_by_another_user(): void
    {
        $other = User::factory()->create();
        SteamUser::factory()->create([
            'user_id' => $other->id,
            'steam_id' => self::STEAM_ID,
        ]);

        Http::fake();

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/steam-user/import', ['steam_id' => self::STEAM_ID])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Steam ID '.self::STEAM_ID.' is already linked to user '.$other->id.'.');

        Http::assertNothingSent();
    }

    public function test_missing_player_returns_not_found(): void
    {
        Http::fake([
            'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/*' => Http::response([
                'response' => ['players' => []],
            ], 200),
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/steam-user/import', ['steam_id' => self::STEAM_ID])
            ->assertNotFound()
            ->assertJsonPath('message', 'Steam player '.self::STEAM_ID.' was not found.');
    }

    public function test_missing_api_key_returns_service_unavailable(): void
    {
        config()->set('steam_web_api.key', '');

        $this->actingAs($this->user, 'api')
            ->postJson('/api/v1/steam-user/import', ['steam_id' => self::STEAM_ID])
            ->assertStatus(503)
            ->assertJsonPath('message', 'STEAM_WEB_API_KEY is not configured.');
    }

    public function test_cli_imports_steam_user_for_specific_local_user(): void
    {
        Http::fake([
            'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/*' => Http::response($this->payload(), 200),
        ]);

        $exitCode = Artisan::call('steam:import-user', [
            'user' => $this->user->id,
            'steam_id' => self::STEAM_ID,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseHas('steam_users', [
            'user_id' => $this->user->id,
            'steam_id' => self::STEAM_ID,
            'persona_name' => 'c0ntr_all',
        ]);
        $this->assertStringContainsString('Steam profile linked to user '.$this->user->id, Artisan::output());
    }

    /**
     * @return array{response: array{players: list<array<string, mixed>>}}
     */
    private function payload(string $personaName = 'c0ntr_all'): array
    {
        return [
            'response' => [
                'players' => [[
                    'steamid' => self::STEAM_ID,
                    'communityvisibilitystate' => 3,
                    'profilestate' => 1,
                    'personaname' => $personaName,
                    'commentpermission' => 1,
                    'profileurl' => 'https://steamcommunity.com/profiles/'.self::STEAM_ID.'/',
                    'avatar' => 'https://avatars.steamstatic.com/8053f456b79059deb9472c0097868240cca7becc.jpg',
                    'avatarmedium' => 'https://avatars.steamstatic.com/8053f456b79059deb9472c0097868240cca7becc_medium.jpg',
                    'avatarfull' => 'https://avatars.steamstatic.com/8053f456b79059deb9472c0097868240cca7becc_full.jpg',
                    'avatarhash' => '8053f456b79059deb9472c0097868240cca7becc',
                    'lastlogoff' => 1793569603,
                    'personastate' => 1,
                    'realname' => 'Серега',
                    'primaryclanid' => '103582791433603671',
                    'timecreated' => 1204724338,
                    'personastateflags' => 0,
                    'loccountrycode' => 'RU',
                    'locstatecode' => '66',
                    'loccityid' => 42316,
                ]],
            ],
        ];
    }
}
