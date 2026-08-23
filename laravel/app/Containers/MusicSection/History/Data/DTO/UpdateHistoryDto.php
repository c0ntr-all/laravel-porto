<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\Data\DTO;

use App\Ship\Parents\DTO\Data;

class UpdateHistoryDto extends Data
{
    public ?int $track_id = null;

    public function __construct()
    {
    }
}
