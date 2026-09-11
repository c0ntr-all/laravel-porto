<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\UI\API\Transformers;

use App\Containers\MovieSection\Genre\Models\Genre;
use League\Fractal\TransformerAbstract;

class GenreTransformer extends TransformerAbstract
{
    public function transform(Genre $genre): array
    {
        return [
            'id' => $genre->id,
            'kp_id' => $genre->kp_id,
            'name' => $genre->name,
            'slug' => $genre->slug,
            'created_at' => $genre->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $genre->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
