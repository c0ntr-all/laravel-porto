<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\Data\DTO;

use App\Ship\Parents\DTO\Data;

class AchievementCreateData extends Data
{
    public string $name;

    public ?string $description = null;

    public string $steam_api_name;

    public function __construct() {}
}
