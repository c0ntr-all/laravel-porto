<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CreateVideoDto extends Data
{
    public ?string $id = null;
    public int|string $user_id;
    public int|string $album_id;
    public string $source;
    public ?string $duration = null;
    public ?string $original_name = null;
    public int $width = 0;
    public int $height = 0;
    public string $extension;
    public ?string $external_url = null;
    public ?string $description = null;
    public ?string $saved_from_id = null;

    public function __construct()
    {
    }
}
