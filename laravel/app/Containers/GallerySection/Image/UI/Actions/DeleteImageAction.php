<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Image\Tasks\DeleteImageTask;
use App\Containers\GallerySection\Image\UI\API\Requests\DeleteImageRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class DeleteImageAction extends UseCaseAction
{
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_IMAGE;

    public function __construct(
        private readonly DeleteImageTask $deleteImageTask,
    ) {
        parent::__construct();
    }

    public function handle(Image $image): bool
    {
        return $this->deleteImageTask->run($image);
    }

    public function asController(Image $image, DeleteImageRequest $request): JsonResponse
    {
        $this->handle($image);

        return response()->json([
            'meta' => [
                'message' => 'Image successfully deleted!',
            ],
        ]);
    }
}
