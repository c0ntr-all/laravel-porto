<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\Data\DTO;

use App\Ship\Parents\DTO\Data;

class PeriodListDto extends Data
{
    public int $user_id;

    public function __construct()
    {
    }
}
