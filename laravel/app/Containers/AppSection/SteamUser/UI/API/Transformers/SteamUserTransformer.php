<?php

declare(strict_types=1);

namespace App\Containers\AppSection\SteamUser\UI\API\Transformers;

use App\Containers\AppSection\SteamUser\Models\SteamUser;
use League\Fractal\TransformerAbstract;

class SteamUserTransformer extends TransformerAbstract
{
    public function transform(SteamUser $steamUser): array
    {
        return [
            'id' => $steamUser->user_id,
            'user_id' => $steamUser->user_id,
            'steam_id' => $steamUser->steam_id,
            'community_visibility_state' => $steamUser->community_visibility_state,
            'profile_state' => $steamUser->profile_state,
            'persona_name' => $steamUser->persona_name,
            'comment_permission' => $steamUser->comment_permission,
            'profile_url' => $steamUser->profile_url,
            'avatar' => $steamUser->avatar,
            'avatar_medium' => $steamUser->avatar_medium,
            'avatar_full' => $steamUser->avatar_full,
            'avatar_hash' => $steamUser->avatar_hash,
            'last_logoff' => $steamUser->last_logoff?->format('Y-m-d H:i:s'),
            'persona_state' => $steamUser->persona_state,
            'real_name' => $steamUser->real_name,
            'primary_clan_id' => $steamUser->primary_clan_id,
            'time_created' => $steamUser->time_created?->format('Y-m-d H:i:s'),
            'persona_state_flags' => $steamUser->persona_state_flags,
            'loc_country_code' => $steamUser->loc_country_code,
            'loc_state_code' => $steamUser->loc_state_code,
            'loc_city_id' => $steamUser->loc_city_id,
            'created_at' => $steamUser->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $steamUser->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
