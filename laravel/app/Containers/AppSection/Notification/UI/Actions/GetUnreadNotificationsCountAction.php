<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\UI\Actions;

use App\Containers\AppSection\Notification\Tasks\CountUnreadNotificationsTask;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetUnreadNotificationsCountAction extends BaseAction
{
    public function __construct(
        private readonly CountUnreadNotificationsTask $countUnreadNotificationsTask,
    ) {
    }

    public function handle(int $userId): int
    {
        return $this->countUnreadNotificationsTask->run($userId);
    }

    public function asController(): JsonResponse
    {
        $count = $this->handle((int) auth()->id());

        return response()->json([
            'data' => [
                'unread_count' => $count,
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }
}
