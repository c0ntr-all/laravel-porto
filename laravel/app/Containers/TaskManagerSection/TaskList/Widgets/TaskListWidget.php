<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Widgets;

use App\Containers\DashboardSection\Widget\Abstracts\AbstractWidget;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;

class TaskListWidget extends AbstractWidget
{
    public function type(): string
    {
        return 'task-manager.task-list';
    }

    public function name(): string
    {
        return 'Список задач';
    }

    public function description(): string
    {
        return 'Открытые задачи конкретного списка. Укажите ID списка в настройках.';
    }

    public function icon(): string
    {
        return 'view_kanban';
    }

    public function defaultSize(): WidgetSizeEnum
    {
        return WidgetSizeEnum::HALF;
    }

    public function configSchema(): array
    {
        return array_merge($this->limitSchema(8, 30), [
            'task_list_id' => [
                'type' => 'integer',
                'label' => 'ID списка задач',
                'min' => 1,
            ],
        ]);
    }

    public function resolve(WidgetContext $context): WidgetPayload
    {
        $listId = (int) $context->config('task_list_id');
        if ($listId < 1) {
            return WidgetPayload::error($this->type(), $this->title($context), 'Укажите ID списка задач в настройках виджета.');
        }

        $list = TaskList::query()->find($listId);

        if ($list === null) {
            return WidgetPayload::error($this->type(), $this->title($context), 'Список задач не найден.');
        }

        $tasks = Task::query()
            ->where('task_list_id', $list->id)
            ->whereNull('finished_at')
            ->where('is_declined', false)
            ->orderByDesc('id')
            ->limit($context->limit())
            ->get();

        $items = $tasks->map(static fn (Task $task) => [
            'id' => (string) $task->id,
            'title' => $task->title,
            'href' => '/task-manager',
        ])->values()->all();

        $title = $context->widget->title ?: $list->title;

        return WidgetPayload::make(
            type: $this->type(),
            title: $title,
            view: $this->view(),
            data: ['items' => $items],
            meta: [
                'count' => count($items),
                'href' => '/task-manager',
                'task_list_id' => $list->id,
            ],
        );
    }
}
