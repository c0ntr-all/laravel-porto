<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Events;

use App\Containers\AppSection\Notification\Models\UserNotification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserNotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly UserNotification $notification,
        public readonly int $unreadCount,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.' . $this->notification->user_id . '.notifications'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'title' => $this->notification->title,
            'body' => $this->notification->body,
            'data' => $this->notification->data ?? [],
            'is_read' => $this->notification->isRead(),
            'read_at' => $this->notification->read_at?->toIso8601String(),
            'created_at' => $this->notification->created_at?->toIso8601String(),
            'unread_count' => $this->unreadCount,
        ];
    }
}
