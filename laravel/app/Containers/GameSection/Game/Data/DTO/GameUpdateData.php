<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class GameUpdateData extends Data
{
    public string|Optional $name;

    public string|Optional|null $description;

    public string|Optional|null $img;

    public string|Optional|null $air_date;

    public string|Optional|null $developer;

    public string|Optional|null $publisher;

    public int|Optional|null $steam_id;

    public function __construct() {}
}
