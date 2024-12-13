<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Data\DTO;

use Spatie\LaravelData\Data;

class CreateImageDto extends Data
{
    public int $user_id;
    public string $source;
    public int $width;
    public int $height;
    public string $original_path;
    public string $list_thumb_path;
    public string $preview_thumb_path;
    public ?string $description = null;

    public function __construct()
    {
    }
}
