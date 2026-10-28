<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ImportRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'steam_id' => 'required|integer|min:1',
        ];
    }
}
