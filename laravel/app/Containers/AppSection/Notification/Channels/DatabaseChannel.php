<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Channels;

use App\Containers\AppSection\Notification\Contracts\NotificationChannelInterface;
use App\Containers\AppSection\Notification\Data\DTO\CreateUserNotificationDto;
use App\Containers\AppSection\Notification\Data\DTO\OutgoingNotificationData;
use App\Containers\AppSection\Notification\Enums\NotificationChannelEnum;
use App\Containers\AppSection\Notification\Support\NotificationRecipientResolver;
use App\Containers\AppSection\Notification\Tasks\CreateUserNotificationTask;

class DatabaseChannel implements NotificationChannelInterface
{
    public function __construct(
        private readonly CreateUserNotificationTask $createUserNotificationTask,
        private readonly NotificationRecipientResolver $recipientResolver,
    ) {
    }

    public function key(): string
    {
        return NotificationChannelEnum::DATABASE->value;
    }

    public function send(object $notifiable, OutgoingNotificationData $notification): void
    {
        $payload = array_merge($notification->data, $notification->meta);

        $this->createUserNotificationTask->run(CreateUserNotificationDto::from([
            'user_id' => $this->recipientResolver->resolveUserId($notifiable),
            'type' => $notification->type,
            'title' => $notification->subject,
            'body' => $notification->body,
            'data' => $payload,
        ]));
    }
}
