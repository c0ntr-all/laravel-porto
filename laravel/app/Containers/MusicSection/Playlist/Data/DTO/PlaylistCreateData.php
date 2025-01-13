<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\Data\DTO;

use App\Ship\Parents\DTO\Data;

class PlaylistCreateData extends Data
{
    public int $user_id;
    public string $name;

    public function __construct(
    ) {
    }
}
