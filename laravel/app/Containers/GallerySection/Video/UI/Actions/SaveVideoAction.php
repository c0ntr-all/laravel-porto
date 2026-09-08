<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\Actions;

use App\Containers\GallerySection\Video\Models\Video;
use App\Containers\GallerySection\Video\Tasks\SaveVideoToSaveAlbumTask;
use App\Containers\GallerySection\Video\UI\API\Requests\SaveVideoRequest;
use App\Containers\GallerySection\Video\UI\API\Transformers\VideoTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SaveVideoAction extends UseCaseAction
{
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_VIDEO;

    public function __construct(
        private readonly SaveVideoToSaveAlbumTask $saveVideoToSaveAlbumTask,
    ) {
        parent::__construct();
    }

    public function handle(Video $video, int $userId): Video
    {
        return DB::transaction(function () use ($video, $userId) {
            $saved = $this->saveVideoToSaveAlbumTask->run($video, $userId);

            if ($saved->wasRecentlyCreated) {
                $this->recordUseCase($saved);
            }

            return $saved;
        });
    }

    public function asController(Video $video, SaveVideoRequest $request): JsonResponse
    {
        $saved = $this->handle($video, (int) auth()->id());
        $created = $saved->wasRecentlyCreated;

        return fractal($saved, new VideoTransformer())
            ->withResourceName(ContainerAliasEnum::GALLERY_VIDEO->value)
            ->addMeta([
                'message' => $created ? 'Video saved!' : 'Video is already in Save album.',
            ])
            ->respond($created ? 201 : 200, [], JSON_PRETTY_PRINT);
    }
}
