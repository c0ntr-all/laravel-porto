<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Data\Factories;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Folder\Models\Folder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Folder>
 */
class FolderFactory extends Factory
{
    protected $model = Folder::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(2, true),
            'slug' => null,
            'is_system' => false,
            'movies_count' => 0,
        ];
    }
}
