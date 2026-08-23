<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\Actions;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Album\Tasks\DeleteAlbumTask;
use App\Containers\MusicSection\Album\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteAlbumAction extends BaseAction
{
    public function __construct(
        private readonly DeleteAlbumTask $deleteAlbumTask
    )
    {
    }

    public function handle(Album $album): ?bool
    {
        return $this->deleteAlbumTask->run($album);
    }

    public function asController(Album $album, DeleteRequest $request): JsonResponse
    {
        $this->handle($album);

        return response()->json([
            'meta' => [
                'message' => 'Album successfully deleted!',
            ],
        ]);
    }
}
