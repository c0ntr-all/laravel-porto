<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $game = $this->route('game');

        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:20000',
            'img' => 'sometimes|nullable|string|max:4096',
            'air_date' => 'sometimes|nullable|date',
            'developer' => 'sometimes|nullable|string|max:255',
            'publisher' => 'sometimes|nullable|string|max:255',
            'steam_id' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
                Rule::unique('games', 'steam_id')->ignore($game),
            ],
        ];
    }
}
