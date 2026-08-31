<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Data\DTO;

use App\Ship\Parents\DTO\Data;

class UploadImageFromWindowsDto extends Data
{
    public int|string $user_id;
    /** @var list<string> */
    public array $paths;

    public function __construct()
    {
    }
}
