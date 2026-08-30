<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Contracts;

use App\Containers\AppSection\Notification\Data\DTO\OutgoingNotificationData;

interface NotificationChannelInterface
{
    public function key(): string;

    public function send(object $notifiable, OutgoingNotificationData $notification): void;
}
