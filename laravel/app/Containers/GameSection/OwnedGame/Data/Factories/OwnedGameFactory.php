<?php

declare(strict_types=1);

namespace App\Containers\GameSection\OwnedGame\Data\Factories;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\OwnedGame\Models\OwnedGame;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OwnedGame>
 */
class OwnedGameFactory extends Factory
{
    protected $model = OwnedGame::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'game_id' => Game::factory(),
            'playtime_forever' => fake()->numberBetween(0, 10000),
            'last_played_at' => fake()->optional()->dateTimeBetween('-2 years'),
        ];
    }
}
