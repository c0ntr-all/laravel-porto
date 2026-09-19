<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CreateImageDto extends Data
{
    public ?string $uuid = null;
    public int|string $user_id;
    public string $source;
    public int $width;
    public int $height;
    public string $extension;
    public ?string $external_url = null;
    public ?string $description = null;
    public int|string|null $saved_from_id = null;

    public function __construct()
    {
    }
}
