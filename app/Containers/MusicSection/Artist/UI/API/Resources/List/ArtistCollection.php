<?php

namespace App\Containers\MusicSection\Artist\UI\API\Resources\List;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ArtistCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'count' => $this->count(),
            'items' => $this->collection,
            'pagination' => [
                'perPage' => $this->perPage(),
                'nextPageUrl' => $this->nextPageUrl(),
                'prevPageUrl' => $this->previousPageUrl(),
                'hasPages' => $this->hasMorePages()
            ]
        ];
    }
}
