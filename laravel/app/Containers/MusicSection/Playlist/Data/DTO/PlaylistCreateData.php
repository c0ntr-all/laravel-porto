<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\Data\DTO;

use Spatie\LaravelData\Data;

class PlaylistCreateData extends Data
{
    public int $user_id;
    public string $name;

    public function __construct(
    ) {
    }
}
