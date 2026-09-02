<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Support;

use App\Containers\AppSection\Notification\Contracts\ReceivesNotifications;
use App\Containers\AppSection\User\Models\User;
use InvalidArgumentException;

final class NotificationRecipientResolver
{
    public function resolveUserId(object $notifiable): int
    {
        if ($notifiable instanceof ReceivesNotifications) {
            return $notifiable->getNotificationUserId();
        }

        if ($notifiable instanceof User) {
            return (int) $notifiable->id;
        }

        if (property_exists($notifiable, 'user_id') && $notifiable->user_id !== null) {
            return (int) $notifiable->user_id;
        }

        if (method_exists($notifiable, 'getKey')) {
            return (int) $notifiable->getKey();
        }

        throw new InvalidArgumentException('Unable to resolve notification recipient user id.');
    }
}
