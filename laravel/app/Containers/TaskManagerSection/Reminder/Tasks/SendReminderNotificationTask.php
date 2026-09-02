<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Tasks;

use App\Containers\AppSection\Notification\Data\DTO\OutgoingNotificationData;
use App\Containers\AppSection\Notification\Enums\NotificationChannelEnum;
use App\Containers\AppSection\Notification\Enums\NotificationTypeEnum;
use App\Containers\AppSection\Notification\Tasks\SendNotificationTask;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SendReminderNotificationTask extends ParentTask
{
    public function __construct(
        private readonly SendNotificationTask $sendNotificationTask
    ) {
    }

    public function run(Reminder $reminder): void
    {
        $reminder->loadMissing(['user', 'task']);

        $user = $reminder->user;
        $task = $reminder->task;

        if ($user === null) {
            return;
        }

        $taskTitle = $task?->title ?? ('Task #' . $reminder->task_id);
        $eventAt = $reminder->datetime?->format('Y-m-d H:i') ?? '—';

        $notification = new OutgoingNotificationData(
            subject: "Reminder: {$taskTitle}",
            body: "This is a reminder for your task \"{$taskTitle}\". Event time: {$eventAt}.",
            type: NotificationTypeEnum::REMINDER_DUE->value,
            data: [
                'reminder_id' => $reminder->id,
                'task_id' => $reminder->task_id,
                'task_title' => $taskTitle,
                'event_at' => $eventAt,
            ],
            meta: [
                'reminder_id' => $reminder->id,
                'task_id' => $reminder->task_id,
                'task_title' => $taskTitle,
                'event_at' => $eventAt,
            ],
        );

        $this->sendNotificationTask->run(
            $user,
            $notification,
            [
                NotificationChannelEnum::DATABASE->value,
                NotificationChannelEnum::EMAIL->value,
            ]
        );
    }
}
