<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Data\DTO;

use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use App\Ship\Parents\DTO\Data;

class CreateAlbumDto extends Data
{
    public int $user_id;
    public ?int $parent_id = null;
    public int $album_type_id = 1;
    public ?string $attributes = null;
    public string $name;
    public ?string $description = null;
    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
    public ?string $date = null;
    public bool $is_date_verified = false;
    public ?string $image = null;
    public string $path;

    public function __construct()
    {
    }
}
