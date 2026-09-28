<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\Data\Factories;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MovieSection\Franchise\Models\Franchise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Franchise>
 */
class FranchiseFactory extends Factory
{
    protected $model = Franchise::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'order' => fake()->numberBetween(0, 100),
        ];
    }
}
