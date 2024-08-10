<?php

namespace App\Containers\MusicSection\Album\UI\API\Resources\Page\Versions;

use Illuminate\Http\Resources\Json\JsonResource;

class AlbumVersionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'date' => $this->date->format('Y-m-d'),
            'image' => $this->full_image,
        ];
    }
}
