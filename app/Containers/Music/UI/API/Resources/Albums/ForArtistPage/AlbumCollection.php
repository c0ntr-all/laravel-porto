<?php

namespace App\Containers\Music\UI\API\Resources\Albums\ForArtistPage;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AlbumCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'count' => $this->count(),
            'items' => $this->collection
        ];
    }
}
