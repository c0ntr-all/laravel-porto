<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\Data\DTO;

use App\Ship\Parents\DTO\Data;

class PlaylistUpdateData extends Data
{
    public ?string $name = null;
    public ?string $description = null;
    public ?string $image = null;

    public function __construct()
    {
    }
}
