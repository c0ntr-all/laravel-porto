<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Data\DTO;

use App\Ship\Parents\DTO\Data;

class UpdateTrackDto extends Data
{
    public ?int $album_id = null;
    public ?int $disc_id = null;
    public ?int $number = null;
    public ?string $name = null;
    public ?string $credits = null;
    public ?string $cd = null;
    public ?string $path = null;
    public ?string $image = null;
    public ?string $duration = null;
    public ?int $bitrate = null;
    public ?string $link = null;
    public ?string $lyrics = null;

    public function __construct()
    {
    }
}
