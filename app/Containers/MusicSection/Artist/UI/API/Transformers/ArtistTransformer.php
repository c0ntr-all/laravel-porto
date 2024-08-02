<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\API\Transformers;

use App\Containers\MusicSection\Artist\Models\Artist;
use League\Fractal\TransformerAbstract;

class ArtistTransformer extends TransformerAbstract
{
    public function transform(Artist $artist): array
    {
        return [
            'id' => $artist->id,
            'name' => $artist->name,
        ];
    }
}
