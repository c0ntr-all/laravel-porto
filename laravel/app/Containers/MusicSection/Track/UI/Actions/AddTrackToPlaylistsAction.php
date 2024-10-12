<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\Actions;

use App\Containers\MusicSection\Track\Data\DTO\AddTrackToPlaylistsData;
use App\Containers\MusicSection\Track\Models\Track;
use App\Containers\MusicSection\Track\Tasks\AddTrackToPlaylistsTask;
use App\Containers\MusicSection\Track\UI\API\Requests\AddTrackToPlaylistsRequest;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class AddTrackToPlaylistsAction
{
    use AsAction;

    public function __construct(
        private readonly AddTrackToPlaylistsTask $addTrackToPlaylistsTask
    )
    {
    }

    public function handle(Track $track, AddTrackToPlaylistsData $dto): array
    {
        return $this->addTrackToPlaylistsTask->run($track, $dto);
    }

    public function asController(AddTrackToPlaylistsRequest $request, Track $track): JsonResponse
    {
        $dto = AddTrackToPlaylistsData::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $this->handle($track, $dto);

        return response()->json(
            ['meta' => [
                'message' => 'Playlists are synchronized!'
            ]]
        );
    }
}
