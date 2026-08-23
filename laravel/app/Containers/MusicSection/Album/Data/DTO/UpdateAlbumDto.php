<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Data\DTO;

use App\Ship\Parents\DTO\Data;

class UpdateAlbumDto extends Data
{
    public ?int $parent_id = null;
    public ?int $album_type_id = null;
    public ?string $name = null;
    public ?string $description = null;
    public mixed $attributes = null;
    public ?string $edition = null;
    public ?string $date = null;
    public ?bool $is_date_verified = null;
    public ?string $image = null;
    public ?string $path = null;

    public function __construct()
    {
    }
}
