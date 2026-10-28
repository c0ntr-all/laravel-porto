<?php

declare(strict_types=1);

namespace App\Containers\AppSection\SteamUser\Tasks;

use App\Containers\AppSection\SteamUser\Data\DTO\SteamUserUpsertData;
use App\Containers\AppSection\SteamUser\Data\Repositories\SteamUserRepository;
use App\Containers\AppSection\SteamUser\Models\SteamUser;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class UpsertSteamUserFromApiTask extends ParentTask
{
    public function __construct(
        private readonly SteamUserRepository $steamUserRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $player  GetPlayerSummaries player object (camelCase keys)
     */
    public function run(User $user, array $player): SteamUser
    {
        $steamId = $this->stringValue($player, 'steamid');
        if ($steamId === null) {
            throw new InvalidArgumentException('Steam player summary is missing steamid.');
        }

        $personaName = $this->stringValue($player, 'personaname');
        if ($personaName === null) {
            throw new InvalidArgumentException('Steam player summary is missing personaname.');
        }

        return $this->steamUserRepository->upsert($user, new SteamUserUpsertData(
            steam_id: $steamId,
            persona_name: $personaName,
            community_visibility_state: $this->intValue($player, 'communityvisibilitystate') ?? 0,
            profile_state: $this->intValue($player, 'profilestate'),
            comment_permission: $this->intValue($player, 'commentpermission'),
            profile_url: $this->stringValue($player, 'profileurl'),
            avatar: $this->stringValue($player, 'avatar'),
            avatar_medium: $this->stringValue($player, 'avatarmedium'),
            avatar_full: $this->stringValue($player, 'avatarfull'),
            avatar_hash: $this->stringValue($player, 'avatarhash'),
            last_logoff: $this->timestampValue($player, 'lastlogoff'),
            persona_state: $this->intValue($player, 'personastate') ?? 0,
            real_name: $this->stringValue($player, 'realname'),
            primary_clan_id: $this->stringValue($player, 'primaryclanid'),
            time_created: $this->timestampValue($player, 'timecreated'),
            persona_state_flags: $this->intValue($player, 'personastateflags'),
            loc_country_code: $this->stringValue($player, 'loccountrycode'),
            loc_state_code: $this->stringValue($player, 'locstatecode'),
            loc_city_id: $this->intValue($player, 'loccityid'),
        ));
    }

    /**
     * @param  array<string, mixed>  $player
     */
    private function stringValue(array $player, string $key): ?string
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
    private function intValue(array $player, string $key): ?int
    {
        if (! array_key_exists($key, $player) || $player[$key] === null || $player[$key] === '') {
            return null;
        }

        if (! is_numeric($player[$key])) {
            return null;
        }

        return (int) $player[$key];
    }

    /**
     * @param  array<string, mixed>  $player
     */
    private function timestampValue(array $player, string $key): ?string
    {
        $unix = $this->intValue($player, $key);
        if ($unix === null || $unix <= 0) {
            return null;
        }

        return Carbon::createFromTimestampUTC($unix)
            ->timezone((string) config('app.timezone'))
            ->toDateTimeString();
    }
}
