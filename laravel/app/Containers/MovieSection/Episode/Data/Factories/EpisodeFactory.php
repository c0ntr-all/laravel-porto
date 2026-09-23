<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\Data\Factories;

use App\Containers\MovieSection\Episode\Models\Episode;
use App\Containers\MovieSection\Season\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Episode>
 */
class EpisodeFactory extends Factory
{
    protected $model = Episode::class;

    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'kp_id' => fake()->unique()->numberBetween(1, 9_999_999),
            'kp_season_id' => fake()->optional()->numberBetween(1, 9_999_999),
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'en_description' => fake()->optional()->paragraph(),
            'number' => fake()->unique()->numberBetween(1, 30),
            'duration' => fake()->optional()->numberBetween(20, 90),
            'air_date' => fake()->optional()->date(),
            'still' => fake()->optional()->url(),
            'still_preview' => fake()->optional()->url(),
        ];
    }
}
