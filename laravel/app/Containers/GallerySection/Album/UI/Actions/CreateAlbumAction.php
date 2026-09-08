<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\UI\Actions;

use App\Containers\GallerySection\Album\Data\DTO\AlbumCreateData;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Album\Tasks\CreateAlbumTask;
use App\Containers\GallerySection\Album\UI\API\Requests\CreateAlbumRequest;
use App\Containers\GallerySection\Album\UI\API\Transformers\AlbumTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CreateAlbumAction extends UseCaseAction
{
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_ALBUM;

    public function __construct(
        private readonly CreateAlbumTask $createAlbumTask,
    ) {
        parent::__construct();
    }

    public function handle(AlbumCreateData $dto): Album
    {
        return DB::transaction(function () use ($dto) {
            $album = $this->createAlbumTask->run($dto);

            $this->recordUseCase($album);

            return $album;
        });
    }

    public function asController(CreateAlbumRequest $request): JsonResponse
    {
        $dto = AlbumCreateData::from($request->validated());
        $dto->user_id = (int) auth()->id();

        $album = $this->handle($dto);

        return fractal($album, new AlbumTransformer())
            ->withResourceName('albums')
            ->addMeta(['message' => 'New album successfully created!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
