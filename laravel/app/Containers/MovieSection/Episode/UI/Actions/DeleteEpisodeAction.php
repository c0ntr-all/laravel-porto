<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\UI\Actions;

use App\Containers\MovieSection\Episode\Models\Episode;
use App\Containers\MovieSection\Episode\Tasks\DeleteEpisodeTask;
use App\Containers\MovieSection\Episode\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteEpisodeAction extends BaseAction
{
    public function __construct(
        private readonly DeleteEpisodeTask $deleteEpisodeTask,
    ) {
    }

    public function handle(Episode $episode): bool
    {
        return $this->deleteEpisodeTask->run($episode);
    }

    public function asController(Episode $episode, DeleteRequest $request): JsonResponse
    {
        $this->handle($episode);

        return response()->json([
            'meta' => [
                'message' => 'Episode successfully deleted!',
            ],
        ]);
    }
}
