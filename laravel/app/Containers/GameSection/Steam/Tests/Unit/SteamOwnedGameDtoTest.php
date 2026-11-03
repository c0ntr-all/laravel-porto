<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Tests\Unit;

use App\Containers\GameSection\Steam\Data\DTO\SteamOwnedGameDto;
use PHPUnit\Framework\TestCase;

class SteamOwnedGameDtoTest extends TestCase
{
    public function test_maps_owned_game_fields(): void
    {
        $dto = SteamOwnedGameDto::fromApi([
            'appid' => 220,
            'playtime_forever' => 643,
            'playtime_windows_forever' => 0,
            'playtime_mac_forever' => 0,
            'playtime_linux_forever' => 0,
            'playtime_deck_forever' => 0,
            'rtime_last_played' => 1550414819,
            'playtime_disconnected' => 0,
        ]);

        $this->assertSame(220, $dto->appid);
        $this->assertSame(643, $dto->playtime_forever);
        $this->assertSame(1550414819, $dto->rtime_last_played);
    }

    public function test_defaults_playtime_when_missing(): void
    {
        $dto = SteamOwnedGameDto::fromApi([
            'appid' => 570,
        ]);

        $this->assertSame(570, $dto->appid);
        $this->assertSame(0, $dto->playtime_forever);
        $this->assertNull($dto->rtime_last_played);
    }
}
