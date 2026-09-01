<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Data\DTO;

use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class UpdateWidgetDto extends Data
{
    public string|Optional|null $title;
    public WidgetSizeEnum|Optional $size;
    public int|Optional $sort_order;
    /** @var array<string, mixed>|Optional */
    public array|Optional $config;
    public bool|Optional $is_enabled;

    public function __construct()
    {
    }
}
