<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\API\Transformers;

use App\Containers\MusicSection\Album\UI\API\Transformers\AlbumInArtistTransformer;
use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistTransformer;
use App\Containers\MusicSection\Upload\Models\MusicUpload;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class UploadTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'artists',
        'albums',
        'tracks',
    ];

    protected array $defaultIncludes = [
        'artists',
    ];

    public function transform(MusicUpload $upload): array
    {
        return [
            'id' => $upload->id,
            'user_id' => $upload->user_id,
            'artist_ids' => $this->ids($upload, 'artists'),
            'album_ids' => $this->ids($upload, 'albums'),
            'artist_name' => $this->artistName($upload),
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
            'albums_total' => (int) (data_get($upload->meta, 'albums_total') ?: $upload->albums_created + $upload->albums_updated),
            'artists_total' => (int) (data_get($upload->meta, 'artists_total') ?: count($this->ids($upload, 'artists'))),
            'error_message' => $upload->error_message,
            'created_at' => $upload->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeArtists(MusicUpload $upload): Collection
    {
        return $this->collection($upload->artists, new ArtistTransformer(), 'artists');
    }

    public function includeAlbums(MusicUpload $upload): Collection
    {
        return $this->collection($upload->albums, new AlbumInArtistTransformer(), 'albums');
    }

    public function includeTracks(MusicUpload $upload): Collection
    {
        return $this->collection($upload->tracks, new UploadTrackTransformer(), 'upload_tracks');
    }

    /**
     * @return list<int>
     */
    private function ids(MusicUpload $upload, string $relation): array
    {
        if (!$upload->relationLoaded($relation)) {
            return [];
        }

        return $upload->{$relation}->pluck('id')->map(static fn (mixed $id) => (int) $id)->values()->all();
    }

    private function artistName(MusicUpload $upload): ?string
    {
        if (!$upload->relationLoaded('artists')) {
            return null;
        }

        $name = $upload->artists->pluck('name')->filter()->implode(' / ');

        return $name !== '' ? $name : null;
    }
}
