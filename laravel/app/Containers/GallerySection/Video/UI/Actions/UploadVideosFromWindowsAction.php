<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUseCaseTask;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Video\Data\DTO\UploadVideoFromWindowsDto;
use App\Containers\GallerySection\Video\Factories\VideoSourceFactory;
use App\Containers\GallerySection\Video\Tasks\PersistVideoFromSourceTask;
use App\Containers\GallerySection\Video\UI\API\Requests\UploadVideosFromWindowsRequest;
use App\Containers\GallerySection\Video\UI\API\Transformers\VideoTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Helpers\WindowsPathHelper;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UploadVideosFromWindowsAction extends UseCaseAction
{
    private const string SOURCE_TYPE = FileSourceEnum::WINDOWS->value;

    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_VIDEO;

    public function __construct(
        private readonly PersistVideoFromSourceTask $persistVideoFromSourceTask,
        private readonly CreateActivityUseCaseTask $createActivityUseCaseTask,
    ) {
        parent::__construct();
    }

    public function handle(Album $album, UploadVideoFromWindowsDto $dto): Collection
    {
        return DB::transaction(function () use ($album, $dto) {
            $result = collect();

            foreach ($dto->paths as $filePath) {
                $windowsPath = WindowsPathHelper::normalizeWindows($filePath);
                $source = VideoSourceFactory::create($windowsPath, self::SOURCE_TYPE);
                $result->push($this->persistVideoFromSourceTask->run(
                    $album,
                    (int) $dto->user_id,
                    self::SOURCE_TYPE,
                    $source,
                ));
            }

            DB::afterCommit(function () use ($result) {
                foreach ($result as $video) {
                    $this->createActivityUseCaseTask->run($video, $this->eventTypesEnum->value);
                }
            });

            return $result;
        });
    }

    public function asController(Album $album, UploadVideosFromWindowsRequest $request): JsonResponse
    {
        $dto = UploadVideoFromWindowsDto::from($request->validated());
        $dto->user_id = (int) auth()->id();

        $videos = $this->handle($album, $dto);

        return fractal($videos, new VideoTransformer())
            ->withResourceName(ContainerAliasEnum::GALLERY_VIDEO->value)
            ->addMeta(['message' => 'Video successfully uploaded!', 'count' => $videos->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
