<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\UI\API\Requests;

use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\Game\Models\Game;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $game = $this->route('game');
        $gameId = $game instanceof Game ? $game->id : $game;
        $achievement = $this->route('achievement');
        $achievementId = $achievement instanceof Achievement ? $achievement->id : $achievement;

        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:20000',
            'steam_api_name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('game_achievements', 'steam_api_name')
                    ->where(fn ($query) => $query->where('game_id', $gameId))
                    ->ignore($achievementId),
            ],
        ];
    }
}
