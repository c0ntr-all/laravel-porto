<?php

namespace App\Containers\MusicSection\Album\UI\API\Resources\Page;

use App\Containers\MusicSection\Album\UI\API\Resources\Page\Tracks\TrackResource;
use App\Containers\MusicSection\Album\UI\API\Resources\Page\Versions\AlbumVersionCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class AlbumResource extends JsonResource
{
    public function toArray($request): array
    {
        $artist = $this->artists->first();

        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'date' => $this->date->format('Y-m-d'),
            'content' => $this->content,
            'image' => $this->full_image,
            'createdAt' => $this->created_at,
            //todo: Сделать массив исполнителей, когда сплит или фит или еще чего
            'artist' => [
                'id' => $artist->id,
                'name' => $artist->name
            ],
            'tracks' => TrackResource::collection($this->tracks),
            'tags' => TagResource::collection($this->tags),
            'versions' => new AlbumVersionCollection($this->versions)
        ];
    }
}
