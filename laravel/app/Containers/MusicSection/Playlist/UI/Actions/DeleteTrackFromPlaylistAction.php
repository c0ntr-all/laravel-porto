<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\UI\Actions;

use App\Containers\MusicSection\Playlist\Data\DTO\DeleteTrackFromPlaylistData;
use App\Containers\MusicSection\Playlist\Models\Playlist;
use App\Containers\MusicSection\Playlist\Tasks\DeleteTrackFromPlaylistTask;
use App\Containers\MusicSection\Playlist\UI\API\Requests\DeleteTrackFromPlaylistRequest;
use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteTrackFromPlaylistAction
{
    use AsAction;

    public function __construct(
        private readonly DeleteTrackFromPlaylistTask $removeTrackFromPlaylistTask
    )
    {
    }

    public function handle(Playlist $playlist, DeleteTrackFromPlaylistData $dto): int
    {
        return $this->removeTrackFromPlaylistTask->run($playlist, $dto);
    }

    public function asController(DeleteTrackFromPlaylistRequest $request, Playlist $playlist, Track $track): JsonResponse
    {
        $dto = new DeleteTrackFromPlaylistData();
        $dto->user_id = auth()->user()->id;
        $dto->track_id = $track->id;

        $this->handle($playlist, $dto);

        return response()->json(
            ['meta' => [
                'message' => 'Track successfully removed!'
            ]]
        );
    }
}
