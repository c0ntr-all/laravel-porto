<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\UI\API\Transformers;

use App\Containers\AppSection\Notification\Models\UserNotification;
use League\Fractal\TransformerAbstract;

class UserNotificationTransformer extends TransformerAbstract
{
    public function transform(UserNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'body' => $notification->body,
            'data' => $notification->data ?? [],
            'is_read' => $notification->isRead(),
            'read_at' => $notification->read_at?->format('Y-m-d H:i:s'),
            'created_at' => $notification->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
