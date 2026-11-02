<?php

declare(strict_types=1);

namespace App\Containers\GameSection\SteamUser\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ImportRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'steam_id' => ['required', 'string', 'max:32', 'regex:/^\d+$/'],
        ];
    }
}
