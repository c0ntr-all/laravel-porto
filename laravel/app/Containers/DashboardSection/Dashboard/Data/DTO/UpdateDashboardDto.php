<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class UpdateDashboardDto extends Data
{
    public string|Optional $name;
    public string|Optional|null $description;
    public bool|Optional $is_default;
    public int|Optional $sort_order;

    public function __construct()
    {
    }
}
