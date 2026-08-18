<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Data\DTO;

use App\Ship\Parents\DTO\Data;

class PresetListDto extends Data
{
    public int $user_id;

    public function __construct()
    {
    }
}
