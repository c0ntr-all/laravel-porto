<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Data\Factories;

use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Preset\Data\ValueObjects\PresetRules;
use App\Containers\LifelogSection\Preset\Models\Preset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Preset>
 */
class PresetFactory extends Factory
{
    protected $model = Preset::class;

    public function definition(): array
    {
        $dateFrom = fake()->dateTimeBetween('-1 year', '-1 month');
        $dateTo = fake()->dateTimeBetween($dateFrom, 'now');

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'color' => fake()->hexColor(),
            'start_date' => $dateFrom,
            'end_date' => $dateTo,
            'rules' => PresetRules::fromArray([
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
            ]),
        ];
    }
}
