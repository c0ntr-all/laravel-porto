<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Data\DTO;

use App\Ship\Parents\DTO\Data;
use InvalidArgumentException;

class SteamOwnedGameDto extends Data
{
    public function __construct(
        public int $appid,
        public int $playtime_forever,
        public ?int $rtime_last_played = null,
    ) {}

    /**
     * @param  array<string, mixed>  $game
     */
    public static function fromApi(array $game): self
    {
        $appId = self::intValue($game, 'appid');
        if ($appId === null || $appId < 1) {
            throw new InvalidArgumentException('Steam owned game is missing a valid appid.');
        }

        return new self(
            appid: $appId,
            playtime_forever: self::intValue($game, 'playtime_forever') ?? 0,
            rtime_last_played: self::intValue($game, 'rtime_last_played'),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function intValue(array $data, string $key): ?int
    {
        if (! array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
            return null;
        }

        if (! is_numeric($data[$key])) {
            return null;
        }

        return (int) $data[$key];
    }
}
