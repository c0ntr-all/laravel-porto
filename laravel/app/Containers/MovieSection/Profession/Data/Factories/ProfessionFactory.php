<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\Data\Factories;

use App\Containers\MovieSection\Profession\Models\Profession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profession>
 */
class ProfessionFactory extends Factory
{
    protected $model = Profession::class;

    public function definition(): array
    {
        return [
            'en_name' => fake()->unique()->lexify('role_??????'),
            'name' => fake()->word(),
        ];
    }
}
