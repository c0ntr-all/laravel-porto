<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Data\DTO;

use App\Containers\GameSection\Import\Enums\GameImportStatusEnum;
use App\Ship\Parents\DTO\Data;

class GameImportCreateData extends Data
{
    public int $user_id;

    public int $steam_id;

    public string $source_url;

    public GameImportStatusEnum $status = GameImportStatusEnum::Pending;

    public function __construct() {}
}
