<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\Actions;

use App\Containers\MusicSection\Tag\Data\DTO\SyncTagsDto;
use App\Containers\MusicSection\Tag\Tasks\SyncTagsTask;
use App\Containers\MusicSection\Track\Data\DTO\UpdateTrackDto;
use App\Containers\MusicSection\Track\Models\Track;
use App\Containers\MusicSection\Track\Tasks\SyncArtistsForTrackTask;
use App\Containers\MusicSection\Track\Tasks\UpdateTrackTask;
use App\Containers\MusicSection\Track\Tasks\UploadTrackCoverTask;
use App\Containers\MusicSection\Track\UI\API\Requests\UpdateRequest;
use App\Containers\MusicSection\Track\UI\API\Transformers\TrackTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UpdateTrackAction extends BaseAction
{
    public function __construct(
        private readonly UpdateTrackTask $updateTrackTask,
        private readonly SyncArtistsForTrackTask $syncArtistsForTrackTask,
        private readonly SyncTagsTask $syncTagsTask,
        private readonly UploadTrackCoverTask $uploadTrackCoverTask
    )
    {
    }

    public function handle(Track $track, array $requestData): Track
    {
        return DB::transaction(function () use ($track, $requestData) {
            $dto = UpdateTrackDto::from($requestData);

            if (!empty($requestData['image_file'])) {
                $dto->image = $this->uploadTrackCoverTask->run($requestData['image_file'], $track->id);
            }

            $track = $this->updateTrackTask->run($track, $dto);

            if (array_key_exists('artist_ids', $requestData)) {
                $this->syncArtistsForTrackTask->run($track, $requestData['artist_ids']);
            }

            if (!empty($requestData['tags'])) {
                $this->syncTagsTask->run($track, SyncTagsDto::from(['tags' => $requestData['tags']]));
            }

            return $track->load(['tags', 'artists', 'rate']);
        });
    }

    public function asController(Track $track, UpdateRequest $request): JsonResponse
    {
        $track = $this->handle($track, $request->validated());

        return fractal($track, new TrackTransformer())
            ->parseIncludes(['tags', 'artists'])
            ->withResourceName('tracks')
            ->addMeta(['message' => 'Track updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
