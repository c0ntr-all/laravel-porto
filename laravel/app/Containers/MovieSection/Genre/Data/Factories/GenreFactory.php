<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\Data\Factories;

use App\Containers\MovieSection\Genre\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Genre>
 */
class GenreFactory extends Factory
{
    protected $model = Genre::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'kp_id' => fake()->unique()->numberBetween(1, 50_000),
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
