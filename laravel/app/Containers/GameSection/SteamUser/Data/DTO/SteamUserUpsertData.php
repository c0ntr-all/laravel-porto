<?php

declare(strict_types=1);

namespace App\Containers\GameSection\SteamUser\Data\DTO;

use App\Ship\Parents\DTO\Data;

class SteamUserUpsertData extends Data
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
        public ?string $last_logoff = null,
        public int $persona_state = 0,
        public ?string $real_name = null,
        public ?string $primary_clan_id = null,
        public ?string $time_created = null,
        public ?int $persona_state_flags = null,
        public ?string $loc_country_code = null,
        public ?string $loc_state_code = null,
        public ?int $loc_city_id = null,
    ) {}
}
