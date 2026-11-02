<?php

declare(strict_types=1);

namespace App\Containers\GameSection\SteamUser\Tests\Functional;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Steam\Data\DTO\SteamPlayerSummaryDto;
use App\Containers\GameSection\SteamUser\Models\SteamUser;
use App\Containers\GameSection\SteamUser\Tasks\UpsertSteamUserFromApiTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SteamUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_read_steam_profile(): void
    {
        $this->getJson('/api/v1/steam-user')->assertUnauthorized();
    }

    public function test_user_without_profile_gets_not_found(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/steam-user')
            ->assertNotFound();
    }

    public function test_import_payload_is_stored_as_has_one_and_returned_for_that_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $steamUser = app(UpsertSteamUserFromApiTask::class)->run($user, SteamPlayerSummaryDto::fromApiPlayer([
            'steamid' => '76561197960435530',
            'communityvisibilitystate' => 3,
            'profilestate' => 1,
            'personaname' => 'Robin',
            'commentpermission' => 1,
            'profileurl' => 'https://steamcommunity.com/id/robin/',
            'avatar' => 'https://example.com/avatar.jpg',
            'avatarmedium' => 'https://example.com/avatar_medium.jpg',
            'avatarfull' => 'https://example.com/avatar_full.jpg',
            'avatarhash' => 'hash',
            'lastlogoff' => 1680000000,
            'personastate' => 1,
            'realname' => 'Robin Walker',
            'primaryclanid' => '103582791429521412',
            'timecreated' => 1063407589,
            'personastateflags' => 0,
            'loccountrycode' => 'US',
            'locstatecode' => 'WA',
            'loccityid' => 3961,
        ]));

        $this->assertTrue($user->steamUser->is($steamUser));
        $this->assertSame(1, SteamUser::query()->count());
        $this->assertTrue(
            $steamUser->time_created?->equalTo(Carbon::createFromTimestampUTC(1063407589)) ?? false,
        );

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/steam-user')
            ->assertOk()
            ->assertJsonPath('data.type', 'steam_users')
            ->assertJsonPath('data.attributes.user_id', $user->id)
            ->assertJsonPath('data.attributes.steam_id', '76561197960435530')
            ->assertJsonPath('data.attributes.persona_name', 'Robin')
            ->assertJsonPath('data.attributes.real_name', 'Robin Walker')
            ->assertJsonPath('data.attributes.loc_country_code', 'US')
            ->assertJsonPath('data.attributes.loc_city_id', 3961);

        $this->actingAs($other, 'api')
            ->getJson('/api/v1/steam-user')
            ->assertNotFound();
    }
}
