<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\Actions;

use App\Containers\AppSection\Tag\Tasks\SyncModelTagsTask;
use App\Containers\GallerySection\Video\Models\Video;
use App\Containers\GallerySection\Video\UI\API\Requests\SyncVideoTagsRequest;
use App\Containers\GallerySection\Video\UI\API\Transformers\VideoTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SyncVideoTagsAction extends UseCaseAction
{
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_VIDEO;

    public function __construct(
        private readonly SyncModelTagsTask $syncModelTagsTask,
    ) {
        parent::__construct();
    }

    public function handle(Video $video, int $userId, array $tagIds, array $newTagNames): Video
    {
        return DB::transaction(function () use ($video, $userId, $tagIds, $newTagNames) {
            $this->syncModelTagsTask->run($video, $userId, $tagIds, $newTagNames);
            $this->recordUseCase($video);

            return $video;
        });
    }

    public function asController(Video $video, SyncVideoTagsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $userId = (int) auth()->id();
        $video = $this->handle(
            $video,
            $userId,
            $validated['tags'] ?? [],
            $validated['new_tags'] ?? [],
        );

        return fractal($video, new VideoTransformer($userId))
            ->withResourceName(ContainerAliasEnum::GALLERY_VIDEO->value)
            ->parseIncludes('tags')
            ->addMeta(['message' => 'Video tags successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
