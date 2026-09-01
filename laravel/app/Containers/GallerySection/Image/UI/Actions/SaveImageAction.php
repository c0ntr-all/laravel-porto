<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUseCaseTask;
use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Image\Tasks\SaveImageToSaveAlbumTask;
use App\Containers\GallerySection\Image\UI\API\Requests\SaveImageRequest;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SaveImageAction extends UseCaseAction
{
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_IMAGE;

    public function __construct(
        private readonly SaveImageToSaveAlbumTask $saveImageToSaveAlbumTask,
        private readonly CreateActivityUseCaseTask $createActivityUseCaseTask,
    ) {
        parent::__construct();
    }

    public function handle(Image $image, int $userId): Image
    {
        return DB::transaction(function () use ($image, $userId) {
            $saved = $this->saveImageToSaveAlbumTask->run($image, $userId);

            if ($saved->wasRecentlyCreated) {
                DB::afterCommit(function () use ($saved) {
                    $this->createActivityUseCaseTask->run($saved, $this->eventTypesEnum->value);
                });
            }

            return $saved;
        });
    }

    public function asController(Image $image, SaveImageRequest $request): JsonResponse
    {
        $saved = $this->handle($image, (int) auth()->id());
        $created = $saved->wasRecentlyCreated;

        return fractal($saved, new ImageTransformer())
            ->withResourceName(ContainerAliasEnum::GALLERY_IMAGE->value)
            ->addMeta([
                'message' => $created ? 'Image saved!' : 'Image is already in Save album.',
            ])
            ->respond($created ? 201 : 200, [], JSON_PRETTY_PRINT);
    }
}
