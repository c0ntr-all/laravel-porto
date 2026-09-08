<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\UI\Actions;

use App\Containers\GallerySection\Album\Data\DTO\AlbumUpdateData;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Album\Tasks\UpdateAlbumTask;
use App\Containers\GallerySection\Album\UI\API\Requests\UpdateAlbumRequest;
use App\Containers\GallerySection\Album\UI\API\Transformers\AlbumTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UpdateAlbumAction extends UseCaseAction
{
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_ALBUM;

    public function __construct(
        private readonly UpdateAlbumTask $updateAlbumTask,
    ) {
        parent::__construct();
    }

    public function handle(Album $album, AlbumUpdateData $dto): Album
    {
        return DB::transaction(function () use ($album, $dto) {
            $updated = $this->updateAlbumTask->run($album, $dto);

            $this->recordUseCase($updated);

            return $updated;
        });
    }

    public function asController(Album $album, UpdateAlbumRequest $request): JsonResponse
    {
        $dto = AlbumUpdateData::from($request->validated());
        $album = $this->handle($album, $dto);

        return fractal($album, new AlbumTransformer())
            ->withResourceName('albums')
            ->addMeta(['message' => 'Album successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
