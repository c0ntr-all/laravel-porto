<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Data\DTO;

use App\Ship\Parents\DTO\Data;

class AddTrackToPlaylistsData extends Data
{
    public   int $user_id;
    public array $playlist_ids;

    public function __construct(
    ) {
    }
}
