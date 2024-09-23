<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\UI\API\Transformers;

use App\Containers\MusicSection\Playlist\Models\Playlist;
use App\Containers\MusicSection\Track\UI\API\Transformers\TrackInPlaylistTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class PlaylistTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'tracks'
    ];

    public function transform(Playlist $playlist): array
    {
        return [
            'id' => $playlist->id,
            'name' => $playlist->name,
            'description' => $playlist->description,
            'image' => $playlist->full_image,
            'created_at' => $playlist->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function includeTracks(Playlist $playlist): Collection
    {
        return $this->collection($playlist->tracks, new TrackInPlaylistTransformer(), 'tracks');
    }
}
