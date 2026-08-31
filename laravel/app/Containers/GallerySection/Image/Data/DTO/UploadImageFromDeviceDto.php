<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Illuminate\Http\UploadedFile;

class UploadImageFromDeviceDto extends Data
{
    public int|string $user_id;
    public UploadedFile $file;

    public function __construct()
    {
    }
}
