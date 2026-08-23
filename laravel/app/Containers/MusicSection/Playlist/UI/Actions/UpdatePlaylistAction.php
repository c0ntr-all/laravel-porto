<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\UI\Actions;

use App\Containers\MusicSection\Playlist\Data\DTO\PlaylistUpdateData;
use App\Containers\MusicSection\Playlist\Models\Playlist;
use App\Containers\MusicSection\Playlist\Tasks\UpdatePlaylistTask;
use App\Containers\MusicSection\Playlist\UI\API\Requests\UpdateRequest;
use App\Containers\MusicSection\Playlist\UI\API\Transformers\PlaylistTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UpdatePlaylistAction extends BaseAction
{
    public function __construct(
        private readonly UpdatePlaylistTask $updatePlaylistTask
    )
    {
    }

    public function handle(Playlist $playlist, PlaylistUpdateData $dto): Playlist
    {
        return $this->updatePlaylistTask->run($playlist, $dto);
    }

    public function asController(Playlist $playlist, UpdateRequest $request): JsonResponse
    {
        $playlist = $this->handle($playlist, PlaylistUpdateData::from($request->validated()));

        return fractal($playlist, new PlaylistTransformer())
            ->withResourceName('playlists')
            ->addMeta(['message' => 'Playlist updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
