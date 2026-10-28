<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Data\Factories;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Import\Enums\GameImportStatusEnum;
use App\Containers\GameSection\Import\Models\GameImport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameImport>
 */
class GameImportFactory extends Factory
{
    protected $model = GameImport::class;

    public function definition(): array
    {
        $steamId = fake()->unique()->numberBetween(1, 2_000_000_000);

        return [
            'user_id' => User::factory(),
            'game_id' => null,
            'steam_id' => $steamId,
            'source_url' => 'https://store.steampowered.com/app/'.$steamId.'?l=english',
            'status' => GameImportStatusEnum::Pending,
            'started_at' => now(),
        ];
    }
}
