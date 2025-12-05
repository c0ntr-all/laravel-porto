<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\Factories;

use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Post\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Containers\AppSection\User\Models\User>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'date' => fake()->date(),
            'time' => fake()->time(),
        ];
    }
}
