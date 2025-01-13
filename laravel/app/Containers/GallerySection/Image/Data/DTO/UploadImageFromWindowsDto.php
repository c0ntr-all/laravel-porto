<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Data\DTO;

use App\Ship\Parents\DTO\Data;

class UploadImageFromWindowsDto extends Data
{
    public string $user_id;
    public array $paths;
    public function __construct()
    {
    }
}
