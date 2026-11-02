<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Tests\Unit;

use App\Containers\GameSection\Steam\Data\DTO\SteamPlayerSummaryDto;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SteamPlayerSummaryDtoTest extends TestCase
{
    public function test_maps_all_player_summary_fields(): void
    {
        $dto = SteamPlayerSummaryDto::fromApiPlayer([
            'steamid' => '76561197997038451',
            'communityvisibilitystate' => 3,
            'profilestate' => 1,
            'personaname' => 'c0ntr_all',
            'commentpermission' => 1,
            'profileurl' => 'https://steamcommunity.com/profiles/76561197997038451/',
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
        ]);

        $this->assertSame('76561197997038451', $dto->steam_id);
        $this->assertSame('c0ntr_all', $dto->persona_name);
        $this->assertSame(3, $dto->community_visibility_state);
        $this->assertSame(1, $dto->profile_state);
        $this->assertSame(1, $dto->comment_permission);
        $this->assertSame('https://steamcommunity.com/profiles/76561197997038451/', $dto->profile_url);
        $this->assertSame('https://avatars.steamstatic.com/8053f456b79059deb9472c0097868240cca7becc.jpg', $dto->avatar);
        $this->assertSame('https://avatars.steamstatic.com/8053f456b79059deb9472c0097868240cca7becc_medium.jpg', $dto->avatar_medium);
        $this->assertSame('https://avatars.steamstatic.com/8053f456b79059deb9472c0097868240cca7becc_full.jpg', $dto->avatar_full);
        $this->assertSame('8053f456b79059deb9472c0097868240cca7becc', $dto->avatar_hash);
        $this->assertSame(1793569603, $dto->last_logoff);
        $this->assertSame(1, $dto->persona_state);
        $this->assertSame('Серега', $dto->real_name);
        $this->assertSame('103582791433603671', $dto->primary_clan_id);
        $this->assertSame(1204724338, $dto->time_created);
        $this->assertSame(0, $dto->persona_state_flags);
        $this->assertSame('RU', $dto->loc_country_code);
        $this->assertSame('66', $dto->loc_state_code);
        $this->assertSame(42316, $dto->loc_city_id);
    }

    public function test_requires_steamid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SteamPlayerSummaryDto::fromApiPlayer(['personaname' => 'x']);
    }
}
