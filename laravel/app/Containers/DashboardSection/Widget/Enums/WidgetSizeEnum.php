<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum WidgetSizeEnum: string
{
    use Arrayable;

    case FULL = 'full';
    case HALF = 'half';
    case THIRD = 'third';

    public function span(): int
    {
        return match ($this) {
            self::FULL => 12,
            self::HALF => 6,
            self::THIRD => 4,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::FULL => 'На весь экран',
            self::HALF => '1/2 экрана',
            self::THIRD => '1/3 экрана',
        };
    }
}
