<?php

namespace App\Http\Resources\Client\Music\Playlists;

use App\Containers\MusicSection\Album\UI\API\Resources\Page\Tracks\TrackResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaylistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'tracks' => TrackResource::collection($this->tracks)
        ];
    }
}
