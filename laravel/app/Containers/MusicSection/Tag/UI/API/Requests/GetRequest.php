<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class GetRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [];
    }
}
