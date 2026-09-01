<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum WidgetViewEnum: string
{
    use Arrayable;

    case LIST = 'list';
    case COUNT = 'count';
    case MEDIA = 'media';
    case HTML = 'html';
    case TEXT = 'text';
    case WELCOME = 'welcome';
    case CUSTOM = 'custom';
}
