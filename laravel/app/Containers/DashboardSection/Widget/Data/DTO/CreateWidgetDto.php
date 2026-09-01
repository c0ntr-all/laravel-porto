<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Data\DTO;

use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Ship\Parents\DTO\Data;

class CreateWidgetDto extends Data
{
    public int $dashboard_id;
    public int $user_id;
    public string $type;
    public ?string $title = null;
    public WidgetSizeEnum $size = WidgetSizeEnum::HALF;
    public int $sort_order = 0;
    /** @var array<string, mixed> */
    public array $config = [];
    public bool $is_enabled = true;

    public function __construct()
    {
    }
}
