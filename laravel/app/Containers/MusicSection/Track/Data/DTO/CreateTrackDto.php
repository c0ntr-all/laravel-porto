<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CreateTrackDto extends Data
{
    public int $user_id;
    public string $cd;
    public int $number;
    public string $name;
    public string $path;
    public ?string $image = null;
    public ?string $duration = null;
    public ?int $bitrate = null;
    public ?string $link = null;
    public ?string $lyrics = null;

    public function __construct(
    ) {
    }
}
