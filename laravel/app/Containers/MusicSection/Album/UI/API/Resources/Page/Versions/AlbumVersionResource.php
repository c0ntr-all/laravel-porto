<?php

namespace App\Containers\MusicSection\Album\UI\API\Resources\Page\Versions;

use Illuminate\Http\Resources\Json\JsonResource;

class AlbumVersionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'edition' => $this->edition,
            'album_type_id' => $this->album_type_id,
            'date' => $this->date?->format('Y-m-d'),
            'image' => $this->full_image,
        ];
    }
}
