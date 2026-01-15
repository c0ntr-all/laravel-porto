<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Illuminate\Http\UploadedFile;

class UploadVideoFromDeviceDto extends Data
{
    public string $user_id;
    public UploadedFile $file;
    public function __construct()
    {
    }
}
