<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\UI\Actions;

use App\Containers\AppSection\Notification\Models\UserNotification;
use App\Containers\AppSection\Notification\Tasks\MarkNotificationAsReadTask;
use App\Containers\AppSection\Notification\UI\API\Transformers\UserNotificationTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class MarkNotificationAsReadAction extends BaseAction
{
    public function __construct(
        private readonly MarkNotificationAsReadTask $markNotificationAsReadTask,
    ) {
    }

    public function handle(string $notificationId, int $userId): UserNotification
    {
        return $this->markNotificationAsReadTask->run($notificationId, $userId);
    }

    public function asController(string $notificationId): JsonResponse
    {
        $notification = $this->handle($notificationId, (int) auth()->id());

        return fractal($notification, new UserNotificationTransformer())
            ->withResourceName('notifications')
            ->addMeta(['message' => 'Notification marked as read.'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
