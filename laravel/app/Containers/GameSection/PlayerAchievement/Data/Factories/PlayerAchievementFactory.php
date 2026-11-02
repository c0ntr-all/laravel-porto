<?php

declare(strict_types=1);

namespace App\Containers\GameSection\PlayerAchievement\Data\Factories;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\PlayerAchievement\Models\PlayerAchievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlayerAchievement>
 */
class PlayerAchievementFactory extends Factory
{
    protected $model = PlayerAchievement::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'achievement_id' => Achievement::factory(),
            'achieved' => fake()->boolean(),
            'unlock_time' => fake()->optional()->dateTimeBetween('-2 years'),
        ];
    }
}
