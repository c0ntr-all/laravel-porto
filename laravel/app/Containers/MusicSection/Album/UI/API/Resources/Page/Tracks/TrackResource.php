<?php

namespace App\Containers\MusicSection\Album\UI\API\Resources\Page\Tracks;

use Illuminate\Http\Resources\Json\JsonResource;

class TrackResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'cd' => $this->cd,
            'disc_id' => $this->disc_id,
            'name' => $this->name,
            'credits' => $this->credits,
            'duration' => $this->duration,
            'link' => $this->link,
            'rate' => $this->rate?->rate ?? 0,
            'artist' => $this->artists?->first()?->name,
            'artists' => $this->artists?->map(static function ($artist): array {
                return [
                    'id' => $artist->id,
                    'name' => $artist->name,
                    'role' => $artist->pivot->role ?? null,
                    'is_author' => isset($artist->pivot->is_author)
                        ? (bool) $artist->pivot->is_author
                        : null,
                ];
            })->values()->all(),
            'image' => $this->full_image,
        ];
    }
}
