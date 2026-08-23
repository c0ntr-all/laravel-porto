<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\API\Transformers;

use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistTransformer;
use App\Containers\MusicSection\Upload\Models\MusicUpload;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

class UploadTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'artist',
        'tracks',
    ];

    public function transform(MusicUpload $upload): array
    {
        return [
            'id' => $upload->id,
            'user_id' => $upload->user_id,
            'artist_id' => $upload->artist_id,
            'artist_name' => $upload->artist_name,
            'source_path' => $upload->source_path,
            'status' => $upload->status->value,
            'started_at' => $upload->started_at?->format('Y-m-d H:i:s'),
            'finished_at' => $upload->finished_at?->format('Y-m-d H:i:s'),
            'duration_ms' => $upload->duration_ms,
            'tracks_found' => $upload->tracks_found,
            'tracks_created' => $upload->tracks_created,
            'tracks_updated' => $upload->tracks_updated,
            'tracks_skipped' => $upload->tracks_skipped,
            'tracks_failed' => $upload->tracks_failed,
            'albums_created' => $upload->albums_created,
            'albums_updated' => $upload->albums_updated,
            'artists_created' => $upload->artists_created,
            'error_message' => $upload->error_message,
            'created_at' => $upload->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeArtist(MusicUpload $upload): Item|NullResource
    {
        if (!$upload->artist) {
            return $this->null();
        }

        return $this->item($upload->artist, new ArtistTransformer(), 'artists');
    }

    public function includeTracks(MusicUpload $upload): Collection
    {
        return $this->collection($upload->tracks, new UploadTrackTransformer(), 'upload_tracks');
    }
}
