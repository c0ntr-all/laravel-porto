<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\API\Transformers;

use App\Containers\MusicSection\Album\UI\API\Transformers\AlbumInArtistTransformer;
use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistTransformer;
use App\Containers\MusicSection\Track\UI\API\Transformers\TrackInPlaylistTransformer;
use App\Containers\MusicSection\Upload\Models\MusicUploadTrack;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

class UploadTrackTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'track',
        'album',
        'artist',
    ];

    public function transform(MusicUploadTrack $item): array
    {
        return [
            'id' => $item->id,
            'upload_id' => $item->upload_id,
            'track_id' => $item->track_id,
            'album_id' => $item->album_id,
            'artist_id' => $item->artist_id,
            'artist_name' => $item->artist_name,
            'album_name' => $item->album_name,
            'track_name' => $item->track_name,
            'source_path' => $item->source_path,
            'status' => $item->status->value,
            'snapshot' => $item->snapshot,
            'error_message' => $item->error_message,
            'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeTrack(MusicUploadTrack $item): Item|NullResource
    {
        if (!$item->track) {
            return $this->null();
        }

        return $this->item($item->track, new TrackInPlaylistTransformer(), 'tracks');
    }

    public function includeAlbum(MusicUploadTrack $item): Item|NullResource
    {
        if (!$item->album) {
            return $this->null();
        }

        return $this->item($item->album, new AlbumInArtistTransformer(), 'albums');
    }

    public function includeArtist(MusicUploadTrack $item): Item|NullResource
    {
        if (!$item->artist) {
            return $this->null();
        }

        return $this->item($item->artist, new ArtistTransformer(), 'artists');
    }
}
