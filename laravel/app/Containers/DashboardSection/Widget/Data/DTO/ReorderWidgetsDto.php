<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Data\DTO;

use App\Ship\Parents\DTO\Data;

class ReorderWidgetsDto extends Data
{
    /** @var list<int> */
    public array $ids;

    public function __construct()
    {
    }
}
