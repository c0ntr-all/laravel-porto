<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\Actions;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Album\Tasks\UpdateOrCreateAlbumDiscTask;
use App\Containers\MusicSection\Tag\Data\DTO\SyncTagsDto;
use App\Containers\MusicSection\Tag\Tasks\SyncTagsTask;
use App\Containers\MusicSection\Track\Data\DTO\CreateTrackDto;
use App\Containers\MusicSection\Track\Models\Track;
use App\Containers\MusicSection\Track\Tasks\CreateTrackTask;
use App\Containers\MusicSection\Track\Tasks\SyncArtistsForTrackTask;
use App\Containers\MusicSection\Track\Tasks\UploadTrackCoverTask;
use App\Containers\MusicSection\Track\UI\API\Requests\CreateRequest;
use App\Containers\MusicSection\Track\UI\API\Transformers\TrackTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateTrackAction extends BaseAction
{
    public function __construct(
        private readonly CreateTrackTask $createTrackTask,
        private readonly UpdateOrCreateAlbumDiscTask $updateOrCreateAlbumDiscTask,
        private readonly SyncArtistsForTrackTask $syncArtistsForTrackTask,
        private readonly SyncTagsTask $syncTagsTask,
        private readonly UploadTrackCoverTask $uploadTrackCoverTask
    )
    {
    }

    public function handle(array $requestData): Track
    {
        return DB::transaction(function () use ($requestData) {
            $album = Album::query()->findOrFail($requestData['album_id']);
            $cd = $requestData['cd'] ?? null;
            $discId = $requestData['disc_id'] ?? null;

            if ($discId === null && $cd !== null && $cd !== '') {
                $disc = $this->updateOrCreateAlbumDiscTask->run($album, (int) $cd ?: 1);
                $discId = $disc->id;
                $cd = (string) $disc->number;
            }

            $dto = CreateTrackDto::from([
                'user_id' => auth()->id(),
                'name' => $requestData['name'],
                'credits' => $requestData['credits'] ?? null,
                'cd' => $cd,
                'disc_id' => $discId,
                'number' => $requestData['number'] ?? null,
                'path' => $requestData['path'] ?? (Str::slug($requestData['name']) ?: null),
                'duration' => $requestData['duration'] ?? null,
                'bitrate' => $requestData['bitrate'] ?? null,
                'link' => $requestData['link'] ?? null,
                'lyrics' => $requestData['lyrics'] ?? null,
            ]);

            $track = $this->createTrackTask->run($album, $dto);

            if (!empty($requestData['image_file'])) {
                $track->image = $this->uploadTrackCoverTask->run($requestData['image_file'], $track->id);
                $track->save();
            }

            if (!empty($requestData['artist_ids'])) {
                $this->syncArtistsForTrackTask->run($track, $requestData['artist_ids']);
            }

            if (!empty($requestData['tags'])) {
                $this->syncTagsTask->run($track, SyncTagsDto::from(['tags' => $requestData['tags']]));
            }

            return $track->load(['tags', 'artists', 'rate']);
        });
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $track = $this->handle($request->validated());

        return fractal($track, new TrackTransformer())
            ->parseIncludes(['tags', 'artists'])
            ->withResourceName('tracks')
            ->addMeta(['message' => 'Track created successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
