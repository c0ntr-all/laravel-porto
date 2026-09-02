<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Contracts;

interface ReceivesNotifications
{
    public function getNotificationUserId(): int;
}
