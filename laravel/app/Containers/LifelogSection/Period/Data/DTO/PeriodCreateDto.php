<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\Data\DTO;

use App\Ship\Parents\DTO\Data;

class PeriodCreateDto extends Data
{
    public int $user_id;
    public string $title;
    public string $color;
    public string $start_post_id;
    public string $end_post_id;

    public function __construct()
    {
    }
}
