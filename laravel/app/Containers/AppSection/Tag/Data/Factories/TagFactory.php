<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Data\Factories;

use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'description' => fake()->optional()->sentence(),
            'color' => fake()->optional()->hexColor(),
        ];
    }
}
