<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\Actions;

use App\Containers\MovieSection\Franchise\Models\Franchise;
use App\Containers\MovieSection\Franchise\Tasks\DeleteFranchiseTask;
use App\Containers\MovieSection\Franchise\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteFranchiseAction extends BaseAction
{
    public function __construct(
        private readonly DeleteFranchiseTask $deleteFranchiseTask,
    ) {
    }

    public function handle(Franchise $franchise): bool
    {
        return $this->deleteFranchiseTask->run($franchise);
    }

    public function asController(Franchise $franchise, DeleteRequest $request): JsonResponse
    {
        $this->handle($franchise);

        return response()->json([
            'meta' => [
                'message' => 'Franchise successfully deleted!',
            ],
        ]);
    }
}
