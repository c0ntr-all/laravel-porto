<?php

namespace App\Containers\MusicSection\Album\UI\API\Resources\Page;

use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tracks_count' => $this->pivot->tracks_count ?? null,
            'percentage' => $this->pivot->percentage ?? null,
            'group' => $this->group ? [
                'id' => $this->group->id,
                'name' => $this->group->name,
                'slug' => $this->group->slug,
            ] : null,
        ];
    }
}
