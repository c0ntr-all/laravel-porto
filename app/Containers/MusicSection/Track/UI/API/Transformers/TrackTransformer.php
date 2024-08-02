<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\API\Transformers;

use App\Containers\MusicSection\Track\Models\Track;
use League\Fractal\TransformerAbstract;

class TrackTransformer extends TransformerAbstract
{
    public function transform(Track $track): array
    {
        return [
            'id' => $track->id,
            'name' => $track->name,
        ];
    }
}
