<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\Data\DTO;

use App\Ship\Parents\DTO\Data;

class GameCreateData extends Data
{
    public string $name;

    public ?string $description = null;

    public ?string $img = null;

    public ?string $air_date = null;

    public ?string $developer = null;

    public ?string $publisher = null;

    public ?int $steam_id = null;

    public function __construct() {}
}
