<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\Actions;

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
use App\Ship\Helpers\UuidV7;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UploadVideoFromDeviceAction extends UseCaseAction
{
    private const string SOURCE_TYPE = FileSourceEnum::DEVICE->value;

    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_VIDEO;

    public function __construct(
        private readonly SaveUploadedVideoTask $saveUploadedVideoTask,
        private readonly PersistVideoFromSourceTask $persistVideoFromSourceTask,
    ) {
        parent::__construct();
    }

    public function handle(Album $album, UploadVideoFromDeviceDto $uploadVideoDto): Video
    {
        return DB::transaction(function () use ($album, $uploadVideoDto) {
            $file = $uploadVideoDto->file;
            $uuid = UuidV7::generate();
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

            if (!$album->isUploadStagingAlbum()) {
                $this->recordUseCase($video);
            }

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
            ->withResourceName(ContainerAliasEnum::GALLERY_VIDEO->value)
            ->addMeta(['message' => 'Video successfully uploaded!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
