<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\UI\API\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TagCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'count' => $this->collection->count(),
            'items' => $this->collection
        ];
    }
}
