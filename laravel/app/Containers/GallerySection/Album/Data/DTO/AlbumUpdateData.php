<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class AlbumUpdateData extends Data
{
    public string|Optional $name;
    public string|Optional|null $description;
    public string|Optional|null $image;

    public function __construct()
    {
    }
}
