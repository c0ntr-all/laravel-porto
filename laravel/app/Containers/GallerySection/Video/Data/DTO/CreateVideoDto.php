<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CreateVideoDto extends Data
{
    public ?string $id = null;
    public int $user_id;
    public int $album_id;
    public string $source;
    public int $width;
    public int $height;
    public string $extension;
    public ?string $description = null;

    public function __construct()
    {
    }
}
