<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class WatchRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [];
    }
}
