<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\UI\Actions;

use App\Containers\MovieSection\Profession\Models\Profession;
use App\Containers\MovieSection\Profession\Tasks\DeleteProfessionTask;
use App\Containers\MovieSection\Profession\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteProfessionAction extends BaseAction
{
    public function __construct(
        private readonly DeleteProfessionTask $deleteProfessionTask,
    ) {
    }

    public function handle(Profession $profession): bool
    {
        return $this->deleteProfessionTask->run($profession);
    }

    public function asController(Profession $profession, DeleteRequest $request): JsonResponse
    {
        $this->handle($profession);

        return response()->json([
            'meta' => [
                'message' => 'Profession successfully deleted!',
            ],
        ]);
    }
}
