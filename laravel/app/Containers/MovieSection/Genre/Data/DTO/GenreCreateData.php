<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\Data\DTO;

use App\Ship\Parents\DTO\Data;

class GenreCreateData extends Data
{
    public string $name;
    public ?string $slug = null;
    public ?int $kp_id = null;

    public function __construct()
    {
    }
}
