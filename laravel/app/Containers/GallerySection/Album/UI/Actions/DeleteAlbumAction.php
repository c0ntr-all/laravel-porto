<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Album\Tasks\DeleteAlbumTask;
use App\Containers\GallerySection\Album\UI\API\Requests\DeleteAlbumRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class DeleteAlbumAction extends UseCaseAction
{
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_ALBUM;

    public function __construct(
        private readonly DeleteAlbumTask $deleteAlbumTask,
    ) {
        parent::__construct();
    }

    public function handle(Album $album): bool
    {
        return $this->deleteAlbumTask->run($album);
    }

    public function asController(Album $album, DeleteAlbumRequest $request): JsonResponse
    {
        $this->handle($album);

        return response()->json([
            'meta' => [
                'message' => 'Album successfully deleted!',
            ],
        ]);
    }
}
