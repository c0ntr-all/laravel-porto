<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\Actions;

use App\Containers\MusicSection\Album\Data\DTO\CreateAlbumDto;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Album\Tasks\AssertAlbumCanBeGroupedUnderTask;
use App\Containers\MusicSection\Album\Tasks\CreateAlbumTask;
use App\Containers\MusicSection\Album\Tasks\SyncArtistsForAlbumTask;
use App\Containers\MusicSection\Album\Tasks\UploadAlbumCoverTask;
use App\Containers\MusicSection\Album\UI\API\Requests\CreateRequest;
use App\Containers\MusicSection\Album\UI\API\Transformers\AlbumTransformer;
use App\Containers\MusicSection\Tag\Data\DTO\SyncTagsDto;
use App\Containers\MusicSection\Tag\Tasks\SyncTagsTask;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateAlbumAction extends BaseAction
{
    public function __construct(
        private readonly CreateAlbumTask $createAlbumTask,
        private readonly SyncArtistsForAlbumTask $syncArtistsForAlbumTask,
        private readonly AssertAlbumCanBeGroupedUnderTask $assertAlbumCanBeGroupedUnderTask,
        private readonly SyncTagsTask $syncTagsTask,
        private readonly UploadAlbumCoverTask $uploadAlbumCoverTask
    )
    {
    }

    public function handle(array $requestData): Album
    {
        return DB::transaction(function () use ($requestData) {
            $this->assertAlbumCanBeGroupedUnderTask->run(
                isset($requestData['parent_id']) ? (int) $requestData['parent_id'] : null,
                $requestData['artist_ids'],
            );

            $dto = CreateAlbumDto::from([
                'user_id' => auth()->id(),
                'name' => $requestData['name'],
                'description' => $requestData['description'] ?? null,
                'parent_id' => $requestData['parent_id'] ?? null,
                'album_type_id' => $requestData['album_type_id'] ?? 1,
                'edition' => $requestData['edition'] ?? null,
                'date' => $requestData['date'] ?? null,
                'is_date_verified' => $requestData['is_date_verified'] ?? false,
                'path' => $requestData['path'] ?? (Str::slug($requestData['name']) ?: 'album-'.uniqid()),
                'image' => null,
            ]);

            $album = $this->createAlbumTask->run($dto);
            $this->syncArtistsForAlbumTask->run($album, $requestData['artist_ids']);

            if (!empty($requestData['image_file'])) {
                $album->image = $this->uploadAlbumCoverTask->run(
                    $requestData['image_file'],
                    (int) $requestData['artist_ids'][0]
                );
                $album->save();
            }

            if (!empty($requestData['tags'])) {
                $this->syncTagsTask->run($album, SyncTagsDto::from(['tags' => $requestData['tags']]));
            }

            return $album->load(['artists', 'tags', 'versions', 'parent']);
        });
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $album = $this->handle($request->validated());

        return fractal($album, new AlbumTransformer())
            ->parseIncludes(['artists', 'tags', 'versions', 'parent'])
            ->withResourceName('albums')
            ->addMeta(['message' => 'Album created successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
