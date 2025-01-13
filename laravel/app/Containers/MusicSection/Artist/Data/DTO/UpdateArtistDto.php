<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Data\DTO;

use App\Ship\Parents\DTO\Data;

class UpdateArtistDto extends Data
{
    public int $user_id;
    public string $name;
    public ?string $description = null;
    public ?string $image;

    public function __construct(
    ) {
    }
}
