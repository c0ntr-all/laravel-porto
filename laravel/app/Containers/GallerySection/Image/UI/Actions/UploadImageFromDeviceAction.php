<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Data\DTO\CreateImageDto;
use App\Containers\GallerySection\Image\Data\DTO\UploadImageFromDeviceDto;
use App\Containers\GallerySection\Image\Enums\ImageThumbTypeEnum;
use App\Containers\GallerySection\Image\Enums\ImageSourceEnum;
use App\Containers\GallerySection\Image\Factories\ImageSourceFactory;
use App\Containers\GallerySection\Image\Models\Image as GalleryImage;
use App\Containers\GallerySection\Image\Services\PathGenerationService;
use App\Containers\GallerySection\Image\Tasks\CreateAllImageThumbsTask;
use App\Containers\GallerySection\Image\Tasks\CreateImageInAlbumTask;
use App\Containers\GallerySection\Image\Tasks\SaveUploadedImageTask;
use App\Containers\GallerySection\Image\UI\API\Requests\UploadImageFromDeviceRequest;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Ramsey\Uuid\Uuid;

class UploadImageFromDeviceAction
{
    use AsAction;

    private const string SOURCE_TYPE = ImageSourceEnum::DEVICE->value;

    public function __construct(
        private readonly SaveUploadedImageTask    $saveUploadedImageTask,
        private readonly CreateImageInAlbumTask   $createImageInAlbumTask,
        private readonly CreateAllImageThumbsTask $createAllImageThumbsTask,
        private readonly PathGenerationService    $pathGenerationService
    )
    {
    }

    public function handle(Album $album, UploadImageFromDeviceDto $uploadImageDto): GalleryImage
    {
        $uuid = Uuid::uuid4()->toString();

        $file = $uploadImageDto->file;
        $albumPath = $this->pathGenerationService->getAlbumFolderPath($uploadImageDto->user_id, $album->id);
        $basePath = $albumPath . '/' . $uuid;
        $filePath = $this->saveUploadedImageTask->run($file, $basePath);

        $imageStrategy = ImageSourceFactory::create($filePath, self::SOURCE_TYPE);

        $thumbnails = $this->createAllImageThumbsTask->run($imageStrategy, $albumPath);

        $createImageDto = CreateImageDto::from([
            'id' => $uuid,
            'user_id' => $uploadImageDto->user_id,
            'original_path' => $filePath,
            'list_thumb_path' => $thumbnails[ImageThumbTypeEnum::LIST->value],
            'preview_thumb_path' => $thumbnails[ImageThumbTypeEnum::PREVIEW->value],
            'width' => $imageStrategy->getImage()->width(),
            'height' => $imageStrategy->getImage()->height(),
            'source' => self::SOURCE_TYPE
        ]);

        return $this->createImageInAlbumTask->run($album, $createImageDto);
    }

    public function asController(Album $album, UploadImageFromDeviceRequest $request): JsonResponse
    {
        $uploadImageDto = UploadImageFromDeviceDto::from($request->validated());
        $uploadImageDto->user_id = (string)auth()->user()->id;

        $result = $this->handle($album, $uploadImageDto);

        return fractal($result, new ImageTransformer())
            ->withResourceName('images')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
