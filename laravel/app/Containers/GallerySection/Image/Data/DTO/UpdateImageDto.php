<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class UpdateImageDto extends Data
{
    public string|Optional|null $description;

    public function __construct()
    {
    }
}
