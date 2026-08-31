<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUseCaseTask;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Video\Data\DTO\UploadVideoFromDeviceDto;
use App\Containers\GallerySection\Video\Factories\VideoSourceFactory;
use App\Containers\GallerySection\Video\Models\Video;
use App\Containers\GallerySection\Video\Tasks\PersistVideoFromSourceTask;
use App\Containers\GallerySection\Video\Tasks\SaveUploadedVideoTask;
use App\Containers\GallerySection\Video\UI\API\Requests\UploadVideoFromDeviceRequest;
use App\Containers\GallerySection\Video\UI\API\Transformers\VideoTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class UploadVideoFromDeviceAction extends UseCaseAction
{
    private const string SOURCE_TYPE = FileSourceEnum::DEVICE->value;

    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_VIDEO;

    public function __construct(
        private readonly SaveUploadedVideoTask $saveUploadedVideoTask,
        private readonly PersistVideoFromSourceTask $persistVideoFromSourceTask,
        private readonly CreateActivityUseCaseTask $createActivityUseCaseTask,
    ) {
        parent::__construct();
    }

    public function handle(Album $album, UploadVideoFromDeviceDto $uploadVideoDto): Video
    {
        return DB::transaction(function () use ($album, $uploadVideoDto) {
            $file = $uploadVideoDto->file;
            $uuid = Uuid::uuid4()->toString();
            $relativePath = $this->saveUploadedVideoTask->run(
                $file,
                (string) $uploadVideoDto->user_id,
                (string) $album->id,
                $uuid,
            );

            $source = VideoSourceFactory::fromDevice(
                $relativePath,
                $file->getClientOriginalName(),
            );

            $video = $this->persistVideoFromSourceTask->run(
                $album,
                (int) $uploadVideoDto->user_id,
                self::SOURCE_TYPE,
                $source,
                $uuid,
            );

            DB::afterCommit(function () use ($video) {
                $this->createActivityUseCaseTask->run($video, $this->eventTypesEnum->value);
            });

            return $video;
        });
    }

    public function asController(Album $album, UploadVideoFromDeviceRequest $request): JsonResponse
    {
        $uploadVideoDto = UploadVideoFromDeviceDto::from([
            ...$request->validated(),
            'user_id' => (int) auth()->id(),
        ]);

        $result = $this->handle($album, $uploadVideoDto);

        return fractal($result, new VideoTransformer())
            ->withResourceName('videos')
            ->addMeta(['message' => 'Video successfully uploaded!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
