<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Data\DTO;

use App\Ship\Parents\DTO\Data;

class UploadImageFromWebDto extends Data
{
    public string $user_id;
    public string $link;
    public function __construct()
    {
    }
}
