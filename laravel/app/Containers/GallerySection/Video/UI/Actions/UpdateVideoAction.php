<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUseCaseTask;
use App\Containers\GallerySection\Video\Data\DTO\UpdateVideoDto;
use App\Containers\GallerySection\Video\Models\Video;
use App\Containers\GallerySection\Video\Tasks\UpdateVideoTask;
use App\Containers\GallerySection\Video\UI\API\Requests\UpdateVideoRequest;
use App\Containers\GallerySection\Video\UI\API\Transformers\VideoTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UpdateVideoAction extends UseCaseAction
{
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_VIDEO;

    public function __construct(
        private readonly UpdateVideoTask $updateVideoTask,
        private readonly CreateActivityUseCaseTask $createActivityUseCaseTask,
    ) {
        parent::__construct();
    }

    public function handle(Video $video, UpdateVideoDto $dto): Video
    {
        return DB::transaction(function () use ($video, $dto) {
            $updated = $this->updateVideoTask->run($video, $dto);

            DB::afterCommit(function () use ($updated) {
                $this->createActivityUseCaseTask->run($updated, $this->eventTypesEnum->value);
            });

            return $updated;
        });
    }

    public function asController(Video $video, UpdateVideoRequest $request): JsonResponse
    {
        $dto = UpdateVideoDto::from($request->validated());
        $video = $this->handle($video, $dto);

        return fractal($video, new VideoTransformer())
            ->withResourceName(ContainerAliasEnum::GALLERY_VIDEO->value)
            ->addMeta(['message' => 'Video successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
