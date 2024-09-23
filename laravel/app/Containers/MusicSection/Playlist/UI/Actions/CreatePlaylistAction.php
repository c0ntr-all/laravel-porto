<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\UI\Actions;

use App\Containers\MusicSection\Playlist\Data\DTO\PlaylistCreateData;
use App\Containers\MusicSection\Playlist\Models\Playlist;
use App\Containers\MusicSection\Playlist\Tasks\CreatePlaylistTask;
use App\Containers\MusicSection\Playlist\UI\API\Requests\CreateRequest;
use App\Containers\MusicSection\Playlist\UI\API\Transformers\PlaylistTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePlaylistAction
{
    use AsAction;

    public function __construct(
        private readonly CreatePlaylistTask $createPlaylistTask
    )
    {
    }

    public function handle(PlaylistCreateData $dto): Playlist
    {
        return $this->createPlaylistTask->run($dto);
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $dto = PlaylistCreateData::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $playlist = $this->handle($dto);

        return fractal($playlist, new PlaylistTransformer())
            ->withResourceName('playlists')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
