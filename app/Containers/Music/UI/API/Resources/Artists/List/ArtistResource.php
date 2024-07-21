<?php

namespace App\Containers\Music\UI\API\Resources\Artists\List;

use App\Containers\Music\UI\API\Resources\Artists\Tags\TagCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'content' => $this->content,
            'image' => $this->full_image,
            'createdAt' => $this->created_at,
            'tags' => new TagCollection($this->tags),
            'albums' => new TagCollection($this->tags),
        ];
    }
}
