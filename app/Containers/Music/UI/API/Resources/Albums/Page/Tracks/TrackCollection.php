<?php declare(strict_types=1);

namespace App\Containers\Music\UI\API\Resources\Albums\Page\Tracks;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TrackCollection extends ResourceCollection
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
