<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\UI\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'content' => $this->content,
            'created_at' => $this->created_at,
        ];
    }
}
