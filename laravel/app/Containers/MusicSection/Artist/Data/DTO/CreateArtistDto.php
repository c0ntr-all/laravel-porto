<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CreateArtistDto extends Data
{
    public int $user_id;
    public string $name;
    public ?string $description = null;
    public ?string $country_id = null;
    public ?string $image = null;
    public string $path;

    public function __construct(
    ) {
    }
}
