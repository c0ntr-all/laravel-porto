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
    public ?string $thumb_status = 'ready';
    public ?int $file_size = null;
    public ?int $file_mtime = null;
    public ?string $taken_at = null;

    public function __construct()
    {
    }
}
