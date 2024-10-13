<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\Data\DTO;

use Spatie\LaravelData\Data;

class DeleteTrackFromPlaylistData extends Data
{
    public string $user_id;
    public string $track_id;

    public function __construct(
    ) {
    }
}
