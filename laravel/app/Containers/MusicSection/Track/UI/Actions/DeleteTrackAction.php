<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\Actions;

use App\Containers\MusicSection\Track\Models\Track;
use App\Containers\MusicSection\Track\Tasks\DeleteTrackTask;
use App\Containers\MusicSection\Track\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteTrackAction extends BaseAction
{
    public function __construct(
        private readonly DeleteTrackTask $deleteTrackTask
    )
    {
    }

    public function handle(Track $track): ?bool
    {
        return $this->deleteTrackTask->run($track);
    }

    public function asController(Track $track, DeleteRequest $request): JsonResponse
    {
        $this->handle($track);

        return response()->json([
            'meta' => [
                'message' => 'Track successfully deleted!',
            ],
        ]);
    }
}
