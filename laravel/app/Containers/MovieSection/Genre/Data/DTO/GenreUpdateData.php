<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class GenreUpdateData extends Data
{
    public string|Optional $name;
    public string|Optional $slug;
    public int|Optional|null $kp_id;

    public function __construct()
    {
    }
}
