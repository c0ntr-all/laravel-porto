<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Data\Factories;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Import\Enums\MovieImportStatusEnum;
use App\Containers\MovieSection\Import\Models\MovieImport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MovieImport>
 */
class MovieImportFactory extends Factory
{
    protected $model = MovieImport::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'movie_id' => null,
            'kp_id' => fake()->unique()->numberBetween(1, 9_999_999),
            'source_url' => 'https://www.kinopoisk.ru/film/'.fake()->numberBetween(1, 9_999_999).'/',
            'status' => MovieImportStatusEnum::Pending,
            'started_at' => now(),
        ];
    }
}
