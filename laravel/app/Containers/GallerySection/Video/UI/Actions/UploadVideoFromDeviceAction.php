<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Video\Data\DTO\CreateVideoDto;
use App\Containers\GallerySection\Video\Data\DTO\UploadVideoFromDeviceDto;
use App\Containers\GallerySection\Video\Models\Video;
use App\Containers\GallerySection\Video\Tasks\CreateVideoTask;
use App\Containers\GallerySection\Video\UI\API\Requests\UploadVideoFromDeviceRequest;
use App\Containers\GallerySection\Video\UI\API\Transformers\VideoTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Ramsey\Uuid\Uuid;

class UploadVideoFromDeviceAction extends BaseAction
{
    private const string SOURCE_TYPE = FileSourceEnum::DEVICE->value;

    public function __construct(
        private readonly CreateVideoTask $createVideoTask,
    )
    {
        parent::__construct();
    }

    public function handle(Album $album, UploadVideoFromDeviceDto $uploadVideoDto): Video
    {
        $file = $uploadVideoDto->file;
        $uuid = Uuid::uuid4()->toString();

        $extension = $file->extension();
        $filePath = "userfiles/{$uploadVideoDto->user_id}/videos/{$album->id}";

        $result = Storage::disk('public')->putFileAs($filePath, $file, $uuid . '.' . $extension);
        $media = FFMpeg::fromDisk('public')->open($result);

        $videoStream = $media->getVideoStream();

        $createVideoDto = CreateVideoDto::from([
            'id' => $uuid,
            'user_id' => $uploadVideoDto->user_id,
            'album_id' => $album->id,
            'extension' => $extension,
            'width' => $videoStream->get('width'),
            'height' => $videoStream->get('height'),
            'source' => self::SOURCE_TYPE
        ]);

        return $this->createVideoTask->run($createVideoDto);
    }

    public function asController(Album $album, UploadVideoFromDeviceRequest $request): JsonResponse
    {
        $uploadVideoDto = UploadVideoFromDeviceDto::from([
            ...$request->validated(),
            'user_id' => (string)auth()->user()->id
        ]);

        $result = $this->handle($album, $uploadVideoDto);

        return fractal($result, new VideoTransformer())
            ->withResourceName(ContainerAliasEnum::GALLERY_VIDEO->value)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
