<?php

declare(strict_types=1);

namespace App\Containers\AppSection\SteamUser\Tasks;

use App\Containers\AppSection\Steam\Data\DTO\SteamPlayerSummaryDto;
use App\Containers\AppSection\SteamUser\Data\DTO\SteamUserUpsertData;
use App\Containers\AppSection\SteamUser\Data\Repositories\SteamUserRepository;
use App\Containers\AppSection\SteamUser\Models\SteamUser;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Carbon;

class UpsertSteamUserFromApiTask extends ParentTask
{
    public function __construct(
        private readonly SteamUserRepository $steamUserRepository,
    ) {}

    public function run(User $user, SteamPlayerSummaryDto $summary): SteamUser
    {
        return $this->steamUserRepository->upsert($user, new SteamUserUpsertData(
            steam_id: $summary->steam_id,
            persona_name: $summary->persona_name,
            community_visibility_state: $summary->community_visibility_state,
            profile_state: $summary->profile_state,
            comment_permission: $summary->comment_permission,
            profile_url: $summary->profile_url,
            avatar: $summary->avatar,
            avatar_medium: $summary->avatar_medium,
            avatar_full: $summary->avatar_full,
            avatar_hash: $summary->avatar_hash,
            last_logoff: $this->timestampValue($summary->last_logoff),
            persona_state: $summary->persona_state,
            real_name: $summary->real_name,
            primary_clan_id: $summary->primary_clan_id,
            time_created: $this->timestampValue($summary->time_created),
            persona_state_flags: $summary->persona_state_flags,
            loc_country_code: $summary->loc_country_code,
            loc_state_code: $summary->loc_state_code,
            loc_city_id: $summary->loc_city_id,
        ));
    }

    private function timestampValue(?int $unix): ?string
    {
        if ($unix === null || $unix <= 0) {
            return null;
        }

        return Carbon::createFromTimestampUTC($unix)
            ->timezone((string) config('app.timezone'))
            ->toDateTimeString();
    }
}
