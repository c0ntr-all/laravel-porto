<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\Data\Factories;

use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Period\Models\Period;
use App\Containers\LifelogSection\Post\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Period>
 */
class PeriodFactory extends Factory
{
    protected $model = Period::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'start_post_id' => Post::factory(),
            'end_post_id' => Post::factory(),
            'title' => fake()->sentence(3),
            'color' => fake()->hexColor(),
        ];
    }
}
