<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Data\DTO;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

class UploadMediaDto extends Data
{
    public string $user_id;
    public UploadedFile $file;
    public function __construct()
    {
    }
}
