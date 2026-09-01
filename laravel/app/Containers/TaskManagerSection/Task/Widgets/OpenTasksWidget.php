<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Widgets;

use App\Containers\DashboardSection\Widget\Abstracts\AbstractWidget;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\TaskManagerSection\Task\Models\Task;

class OpenTasksWidget extends AbstractWidget
{
    public function type(): string
    {
        return 'task-manager.open-tasks';
    }

    public function name(): string
    {
        return 'Открытые задачи';
    }

    public function description(): string
    {
        return 'Незавершённые задачи Task Manager.';
    }

    public function icon(): string
    {
        return 'list';
    }

    public function defaultSize(): WidgetSizeEnum
    {
        return WidgetSizeEnum::THIRD;
    }

    public function configSchema(): array
    {
        return $this->limitSchema(8, 30);
    }

    public function resolve(WidgetContext $context): WidgetPayload
    {
        $tasks = Task::query()
            ->whereNull('finished_at')
            ->where('is_declined', false)
            ->orderByDesc('id')
            ->limit($context->limit())
            ->get();

        $items = $tasks->map(static fn (Task $task) => [
            'id' => (string) $task->id,
            'title' => $task->title,
            'subtitle' => $task->task_list_id ? 'В списке' : 'Inbox',
            'href' => '/task-manager',
        ])->values()->all();

        return $this->listPayload($context, $items, [
            'href' => '/task-manager',
            'total' => Task::query()->whereNull('finished_at')->where('is_declined', false)->count(),
        ]);
    }
}
