<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Widgets;

use App\Containers\DashboardSection\Widget\Abstracts\AbstractWidget;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetContext;
use App\Containers\DashboardSection\Widget\Data\ValueObjects\WidgetPayload;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;

class UpcomingRemindersWidget extends AbstractWidget
{
    public function type(): string
    {
        return 'task-manager.upcoming-reminders';
    }

    public function name(): string
    {
        return 'Ближайшие напоминания';
    }

    public function description(): string
    {
        return 'Активные напоминания Task Manager.';
    }

    public function icon(): string
    {
        return 'notifications';
    }

    public function defaultSize(): WidgetSizeEnum
    {
        return WidgetSizeEnum::THIRD;
    }

    public function configSchema(): array
    {
        return $this->limitSchema(5, 20);
    }

    public function resolve(WidgetContext $context): WidgetPayload
    {
        $reminders = Reminder::query()
            ->with('task')
            ->where('is_active', true)
            ->whereNotNull('next_remind_at')
            ->orderBy('next_remind_at')
            ->limit($context->limit())
            ->get();

        $items = $reminders->map(static fn (Reminder $reminder) => [
            'id' => (string) $reminder->id,
            'title' => $reminder->task?->title ?: 'Напоминание',
            'subtitle' => $reminder->next_remind_at?->format('d.m.Y H:i'),
            'href' => '/task-manager',
        ])->values()->all();

        return $this->listPayload($context, $items, ['href' => '/task-manager']);
    }
}
