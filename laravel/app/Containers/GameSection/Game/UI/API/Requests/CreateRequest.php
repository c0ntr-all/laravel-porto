<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'sometimes|nullable|string|max:20000',
            'img' => 'sometimes|nullable|string|max:4096',
            'air_date' => 'sometimes|nullable|date',
            'developer' => 'sometimes|nullable|string|max:255',
            'publisher' => 'sometimes|nullable|string|max:255',
            'steam_id' => 'sometimes|nullable|integer|min:1|unique:games,steam_id',
        ];
    }
}
