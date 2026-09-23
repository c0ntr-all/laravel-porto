<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\Data\Factories;

use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Season\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Season>
 */
class SeasonFactory extends Factory
{
    protected $model = Season::class;

    public function definition(): array
    {
        return [
            'movie_id' => Movie::factory(),
            'kp_id' => fake()->unique()->numberBetween(1, 9_999_999),
            'kp_season_id' => fake()->unique()->numberBetween(1, 9_999_999),
            'kp_movie_id' => fake()->optional()->numberBetween(1, 9_999_999),
            'name' => 'Сезон '.fake()->numberBetween(1, 10),
            'en_name' => 'Season '.fake()->numberBetween(1, 10),
            'number' => fake()->unique()->numberBetween(1, 50),
            'air_date' => fake()->optional()->date(),
            'episodes_count' => fake()->numberBetween(1, 24),
            'duration' => fake()->optional()->numberBetween(20, 70),
            'poster' => fake()->optional()->url(),
            'poster_preview' => fake()->optional()->url(),
        ];
    }
}
