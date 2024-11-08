<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Data\DTO;

use Spatie\LaravelData\Data;

class UploadMediaFromWebDto extends Data
{
    public int $user_id;
    public string $link;
    public function __construct()
    {
    }
}
