<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\UI\Actions;

use App\Containers\MovieSection\Season\Models\Season;
use App\Containers\MovieSection\Season\Tasks\DeleteSeasonTask;
use App\Containers\MovieSection\Season\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteSeasonAction extends BaseAction
{
    public function __construct(
        private readonly DeleteSeasonTask $deleteSeasonTask,
    ) {
    }

    public function handle(Season $season): bool
    {
        return $this->deleteSeasonTask->run($season);
    }

    public function asController(Season $season, DeleteRequest $request): JsonResponse
    {
        $this->handle($season);

        return response()->json([
            'meta' => [
                'message' => 'Season successfully deleted!',
            ],
        ]);
    }
}
