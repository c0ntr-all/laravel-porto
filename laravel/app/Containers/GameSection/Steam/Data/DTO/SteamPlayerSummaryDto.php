<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Data\DTO;

use App\Ship\Parents\DTO\Data;
use InvalidArgumentException;

class SteamPlayerSummaryDto extends Data
{
    public function __construct(
        public string $steam_id,
        public string $persona_name,
        public int $community_visibility_state = 0,
        public ?int $profile_state = null,
        public ?int $comment_permission = null,
        public ?string $profile_url = null,
        public ?string $avatar = null,
        public ?string $avatar_medium = null,
        public ?string $avatar_full = null,
        public ?string $avatar_hash = null,
        public ?int $last_logoff = null,
        public int $persona_state = 0,
        public ?string $real_name = null,
        public ?string $primary_clan_id = null,
        public ?int $time_created = null,
        public ?int $persona_state_flags = null,
        public ?string $loc_country_code = null,
        public ?string $loc_state_code = null,
        public ?int $loc_city_id = null,
    ) {}

    /**
     * @param  array<string, mixed>  $player  Steam API camelCase player object
     */
    public static function fromApiPlayer(array $player): self
    {
        $steamId = self::stringValue($player, 'steamid');
        if ($steamId === null) {
            throw new InvalidArgumentException('Steam player summary is missing steamid.');
        }

        $personaName = self::stringValue($player, 'personaname');
        if ($personaName === null) {
            throw new InvalidArgumentException('Steam player summary is missing personaname.');
        }

        return new self(
            steam_id: $steamId,
            persona_name: $personaName,
            community_visibility_state: self::intValue($player, 'communityvisibilitystate') ?? 0,
            profile_state: self::intValue($player, 'profilestate'),
            comment_permission: self::intValue($player, 'commentpermission'),
            profile_url: self::stringValue($player, 'profileurl'),
            avatar: self::stringValue($player, 'avatar'),
            avatar_medium: self::stringValue($player, 'avatarmedium'),
            avatar_full: self::stringValue($player, 'avatarfull'),
            avatar_hash: self::stringValue($player, 'avatarhash'),
            last_logoff: self::intValue($player, 'lastlogoff'),
            persona_state: self::intValue($player, 'personastate') ?? 0,
            real_name: self::stringValue($player, 'realname'),
            primary_clan_id: self::stringValue($player, 'primaryclanid'),
            time_created: self::intValue($player, 'timecreated'),
            persona_state_flags: self::intValue($player, 'personastateflags'),
            loc_country_code: self::stringValue($player, 'loccountrycode'),
            loc_state_code: self::stringValue($player, 'locstatecode'),
            loc_city_id: self::intValue($player, 'loccityid'),
        );
    }

    /**
     * @param  array<string, mixed>  $player
     */
    private static function stringValue(array $player, string $key): ?string
    {
        if (! array_key_exists($key, $player) || $player[$key] === null) {
            return null;
        }

        $value = trim((string) $player[$key]);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $player
     */
    private static function intValue(array $player, string $key): ?int
    {
        if (! array_key_exists($key, $player) || $player[$key] === null || $player[$key] === '') {
            return null;
        }

        if (! is_numeric($player[$key])) {
            return null;
        }

        return (int) $player[$key];
    }
}
