<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\Actions;

use App\Containers\MusicSection\Album\Data\DTO\UpdateAlbumDto;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Album\Tasks\AssertAlbumCanBeGroupedUnderTask;
use App\Containers\MusicSection\Album\Tasks\SyncArtistsForAlbumTask;
use App\Containers\MusicSection\Album\Tasks\UpdateAlbumTask;
use App\Containers\MusicSection\Album\Tasks\UploadAlbumCoverTask;
use App\Containers\MusicSection\Album\UI\API\Requests\UpdateRequest;
use App\Containers\MusicSection\Album\UI\API\Transformers\AlbumTransformer;
use App\Containers\MusicSection\Tag\Data\DTO\SyncTagsDto;
use App\Containers\MusicSection\Tag\Tasks\SyncTagsTask;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UpdateAlbumAction extends BaseAction
{
    public function __construct(
        private readonly UpdateAlbumTask $updateAlbumTask,
        private readonly SyncArtistsForAlbumTask $syncArtistsForAlbumTask,
        private readonly AssertAlbumCanBeGroupedUnderTask $assertAlbumCanBeGroupedUnderTask,
        private readonly SyncTagsTask $syncTagsTask,
        private readonly UploadAlbumCoverTask $uploadAlbumCoverTask
    )
    {
    }

    public function handle(Album $album, array $requestData): Album
    {
        return DB::transaction(function () use ($album, $requestData) {
            $artistIds = $requestData['artist_ids']
                ?? $album->artists()->pluck('music_artists.id')->map(static fn (mixed $id): int => (int) $id)->all();
            $parentId = array_key_exists('parent_id', $requestData)
                ? ($requestData['parent_id'] !== null ? (int) $requestData['parent_id'] : null)
                : ($album->parent_id !== null ? (int) $album->parent_id : null);

            $this->assertAlbumCanBeGroupedUnderTask->run($parentId, $artistIds, $album);

            $dto = UpdateAlbumDto::from($requestData);

            if (!empty($requestData['image_file'])) {
                $artistId = $requestData['artist_ids'][0]
                    ?? $album->artists()->value('music_artists.id')
                    ?? $album->id;

                $dto->image = $this->uploadAlbumCoverTask->run(
                    $requestData['image_file'],
                    (int) $artistId
                );
            }

            $album = $this->updateAlbumTask->run($album, $dto);

            if (array_key_exists('artist_ids', $requestData)) {
                $this->syncArtistsForAlbumTask->run($album, $requestData['artist_ids']);
            }

            if (array_key_exists('tags', $requestData)) {
                $this->syncTagsTask->run($album, SyncTagsDto::from(['tags' => $requestData['tags'] ?? []]));
            }

            return $album->load(['artists', 'tags', 'versions.albumType', 'parent.albumType', 'albumType']);
        });
    }

    public function asController(Album $album, UpdateRequest $request): JsonResponse
    {
        $album = $this->handle($album, $request->validated());

        return fractal($album, new AlbumTransformer())
            ->parseIncludes(['artists', 'tags', 'versions', 'parent'])
            ->withResourceName('albums')
            ->addMeta(['message' => 'Album updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
