<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\Data\Factories;

use App\Containers\GameSection\Game\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'img' => fake()->imageUrl(),
            'air_date' => fake()->date(),
            'developer' => fake()->company(),
            'publisher' => fake()->company(),
            'steam_id' => fake()->unique()->numberBetween(1, 2_000_000_000),
        ];
    }
}
