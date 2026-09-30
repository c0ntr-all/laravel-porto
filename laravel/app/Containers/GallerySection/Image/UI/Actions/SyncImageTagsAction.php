<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\AppSection\Tag\Tasks\SyncModelTagsTask;
use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Image\UI\API\Requests\SyncImageTagsRequest;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SyncImageTagsAction extends UseCaseAction
{
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_IMAGE;

    public function __construct(
        private readonly SyncModelTagsTask $syncModelTagsTask,
    ) {
        parent::__construct();
    }

    public function handle(Image $image, int $userId, array $tagIds, array $newTagNames): Image
    {
        return DB::transaction(function () use ($image, $userId, $tagIds, $newTagNames) {
            $this->syncModelTagsTask->run($image, $userId, $tagIds, $newTagNames);
            $this->recordUseCase($image);

            return $image;
        });
    }

    public function asController(Image $image, SyncImageTagsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $userId = (int) auth()->id();
        $image = $this->handle(
            $image,
            $userId,
            $validated['tags'] ?? [],
            $validated['new_tags'] ?? [],
        );

        return fractal($image, new ImageTransformer($userId))
            ->withResourceName(ContainerAliasEnum::GALLERY_IMAGE->value)
            ->parseIncludes('tags')
            ->addMeta(['message' => 'Image tags successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
