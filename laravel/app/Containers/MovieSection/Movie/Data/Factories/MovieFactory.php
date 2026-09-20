<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Data\Factories;

use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Containers\MovieSection\Movie\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movie>
 */
class MovieFactory extends Factory
{
    protected $model = Movie::class;

    public function definition(): array
    {
        return [
            'kp_id' => fake()->unique()->numberBetween(1, 9_999_999),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'short_description' => fake()->optional()->sentence(),
            'year' => fake()->numberBetween(1950, (int) date('Y')),
            'type' => fake()->randomElement(MovieTypeEnum::cases()),
            'cover' => fake()->optional()->url(),
            'kp_rating' => fake()->optional()->randomFloat(1, 1, 10),
            'kp_img' => fake()->optional()->url(),
        ];
    }
}
