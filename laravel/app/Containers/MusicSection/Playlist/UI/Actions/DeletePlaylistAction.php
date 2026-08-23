<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\UI\Actions;

use App\Containers\MusicSection\Playlist\Models\Playlist;
use App\Containers\MusicSection\Playlist\Tasks\DeletePlaylistTask;
use App\Containers\MusicSection\Playlist\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeletePlaylistAction extends BaseAction
{
    public function __construct(
        private readonly DeletePlaylistTask $deletePlaylistTask
    )
    {
    }

    public function handle(Playlist $playlist): ?bool
    {
        return $this->deletePlaylistTask->run($playlist);
    }

    public function asController(Playlist $playlist, DeleteRequest $request): JsonResponse
    {
        $this->handle($playlist);

        return response()->json([
            'meta' => [
                'message' => 'Playlist successfully deleted!',
            ],
        ]);
    }
}
