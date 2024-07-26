<?php

namespace App\Containers\MusicSection\Album\UI\API\Resources\ForArtistPage;

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
