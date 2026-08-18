<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Data\Factories;

use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Preset\Models\Preset;
use App\Containers\LifelogSection\Post\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Preset>
 */
class PresetFactory extends Factory
{
    protected $model = Preset::class;

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
