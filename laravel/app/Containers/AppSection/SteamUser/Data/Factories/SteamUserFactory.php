<?php

declare(strict_types=1);

namespace App\Containers\AppSection\SteamUser\Data\Factories;

use App\Containers\AppSection\SteamUser\Models\SteamUser;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SteamUser>
 */
class SteamUserFactory extends Factory
{
    protected $model = SteamUser::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'steam_id' => (string) fake()->unique()->numerify('7656119##########'),
            'community_visibility_state' => 3,
            'profile_state' => 1,
            'persona_name' => fake()->userName(),
            'comment_permission' => 1,
            'profile_url' => fake()->url(),
            'avatar' => fake()->imageUrl(32, 32),
            'avatar_medium' => fake()->imageUrl(64, 64),
            'avatar_full' => fake()->imageUrl(184, 184),
            'avatar_hash' => fake()->md5(),
            'last_logoff' => now()->subDay(),
            'persona_state' => 1,
            'real_name' => fake()->name(),
            'primary_clan_id' => (string) fake()->numerify('1035827914########'),
            'time_created' => now()->subYears(5),
            'persona_state_flags' => 0,
            'loc_country_code' => 'US',
            'loc_state_code' => 'WA',
            'loc_city_id' => 3961,
        ];
    }
}
