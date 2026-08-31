<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Data\DTO;

use App\Ship\Parents\DTO\Data;

class AlbumCreateData extends Data
{
    public int $user_id;
    public string $name;
    public ?string $description = null;
    public ?string $image = null;

    public function __construct()
    {
    }
}
