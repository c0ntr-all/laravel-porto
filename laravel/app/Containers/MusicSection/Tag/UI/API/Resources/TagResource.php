<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'group_id' => $this->group_id,
            'createdAt' => $this->created_at,
        ];
    }
}
