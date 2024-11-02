<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Data\DTO;

use App\Containers\GallerySection\Media\Enums\MediaTypeEnum;
use Spatie\LaravelData\Data;

class CreateMediaDto extends Data
{
    public int $user_id;
    public MediaTypeEnum $type;
    public string $path;
    public bool $is_web;
    public ?string $description = null;

    public function __construct()
    {
    }
}
