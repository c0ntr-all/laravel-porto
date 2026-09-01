<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Tasks;

use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Exceptions\WidgetNotFoundException;
use App\Containers\DashboardSection\Widget\Managers\WidgetRegistry;
use App\Containers\DashboardSection\Widget\Models\Widget;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Throwable;

class ResolveWidgetPayloadTask extends ParentTask
{
    public function __construct(
        private readonly WidgetRegistry $widgetRegistry,
    ) {
    }

    public function run(Widget $widget): WidgetPayload
    {
        try {
            $definition = $this->widgetRegistry->get($widget->type);
        } catch (WidgetNotFoundException) {
            return WidgetPayload::error($widget->type, $widget->title ?: $widget->type, 'Тип виджета больше не зарегистрирован.');
        }

        $user = $widget->user ?? auth()->user();

        if ($user === null) {
            return WidgetPayload::error($widget->type, $widget->title ?: $definition->name(), 'Пользователь не найден.');
        }

        $context = new WidgetContext(
            user: $user,
            widget: $widget,
            config: is_array($widget->config) ? $widget->config : [],
        );

        try {
            return $definition->resolve($context);
        } catch (Throwable $exception) {
            return WidgetPayload::error(
                $widget->type,
                $widget->title ?: $definition->name(),
                'Не удалось загрузить данные виджета.',
            );
        }
    }
}
