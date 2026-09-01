<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum WidgetCategoryEnum: string
{
    use Arrayable;

    case DASHBOARD = 'dashboard';
    case LIFELOG = 'lifelog';
    case TASK_MANAGER = 'task-manager';
    case MUSIC = 'music';
    case GALLERY = 'gallery';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::DASHBOARD => 'Dashboard',
            self::LIFELOG => 'Lifelog',
            self::TASK_MANAGER => 'Task Manager',
            self::MUSIC => 'Music',
            self::GALLERY => 'Gallery',
            self::CUSTOM => 'Custom',
        };
    }
}
