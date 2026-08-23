<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CreateHistoryDto extends Data
{
    public int $user_id;
    public int $track_id;

    public function __construct()
    {
    }
}
