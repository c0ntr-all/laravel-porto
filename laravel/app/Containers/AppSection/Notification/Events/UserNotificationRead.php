<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class UserNotificationRead implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public readonly int $userId,
        public readonly string $notificationId,
        public readonly int $unreadCount,
        public readonly bool $markAll = false,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.' . $this->userId . '.notifications'),
        ];
    }

    public function broadcastAs(): string
    {
        return $this->markAll ? 'notifications.read_all' : 'notification.read';
    }

    public function broadcastWith(): array
    {
        return [
            'notification_id' => $this->markAll ? null : $this->notificationId,
            'unread_count' => $this->unreadCount,
        ];
    }
}
