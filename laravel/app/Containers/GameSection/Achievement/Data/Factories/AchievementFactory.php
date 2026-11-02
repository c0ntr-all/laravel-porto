<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\Data\Factories;

use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\Game\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Achievement>
 */
class AchievementFactory extends Factory
{
    protected $model = Achievement::class;

    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'steam_api_name' => strtoupper(fake()->unique()->bothify('ACH_????_###')),
            'hidden' => false,
            'archived' => false,
        ];
    }
}
