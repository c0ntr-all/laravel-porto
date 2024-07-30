<?php

namespace App\Containers\MusicSection\Artist\UI\API\Resources\Page\Albums;

use Illuminate\Http\Resources\Json\JsonResource;

class AlbumResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'year' => $this->date->format('Y'),
            'image' => $this->full_image,
        ];
    }
}
