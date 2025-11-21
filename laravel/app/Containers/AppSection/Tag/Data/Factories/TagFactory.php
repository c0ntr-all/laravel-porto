<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Data\Factories;

use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Containers\AppSection\User\Models\User>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->sentence(),
            'slug' => fake()->slug(),
            'content' => fake()->paragraph(),
        ];
    }
}
