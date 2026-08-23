<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class IndexRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [];
    }
}
