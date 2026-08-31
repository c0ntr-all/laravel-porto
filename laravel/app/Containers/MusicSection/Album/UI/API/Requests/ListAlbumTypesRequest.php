<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ListAlbumTypesRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [];
    }
}
