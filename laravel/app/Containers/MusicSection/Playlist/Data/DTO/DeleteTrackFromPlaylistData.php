<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\Data\DTO;

use Spatie\LaravelData\Data;

class DeleteTrackFromPlaylistData extends Data
{
    public int $user_id;
    public int $track_id;

    public function __construct(
    ) {
    }
}
