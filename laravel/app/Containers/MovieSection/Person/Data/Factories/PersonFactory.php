<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\Data\Factories;

use App\Containers\MovieSection\Person\Models\Person;
use App\Containers\MovieSection\Profession\Models\Profession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Person>
 */
class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'kp_id' => fake()->unique()->numberBetween(1, 9_999_999),
            'profession_id' => Profession::factory(),
            'name' => fake()->name(),
            'en_name' => fake()->optional()->name(),
            'photo' => fake()->optional()->imageUrl(),
        ];
    }
}
