<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CreateDashboardDto extends Data
{
    public int $user_id;
    public string $name;
    public ?string $description = null;
    public bool $is_default = false;
    public int $sort_order = 0;
    public bool $with_defaults = false;

    public function __construct()
    {
    }
}
