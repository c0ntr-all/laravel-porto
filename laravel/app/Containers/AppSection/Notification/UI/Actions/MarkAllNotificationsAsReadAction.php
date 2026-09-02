<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\UI\Actions;

use App\Containers\AppSection\Notification\Tasks\MarkAllNotificationsAsReadTask;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class MarkAllNotificationsAsReadAction extends BaseAction
{
    public function __construct(
        private readonly MarkAllNotificationsAsReadTask $markAllNotificationsAsReadTask,
    ) {
    }

    public function handle(int $userId): int
    {
        return $this->markAllNotificationsAsReadTask->run($userId);
    }

    public function asController(): JsonResponse
    {
        $updated = $this->handle((int) auth()->id());

        return response()->json([
            'data' => [
                'updated_count' => $updated,
            ],
            'meta' => [
                'message' => 'All notifications marked as read.',
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }
}
