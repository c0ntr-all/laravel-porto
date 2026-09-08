<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Data\DTO\CreateImageDto;
use App\Containers\GallerySection\Image\Data\DTO\UploadImageFromDeviceDto;
use App\Containers\GallerySection\Image\Enums\ImageMimeEnum;
use App\Containers\GallerySection\Image\Factories\ImageSourceFactory;
use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Image\Services\PathGenerationService;
use App\Containers\GallerySection\Image\Tasks\CreateAllImageThumbsTask;
use App\Containers\GallerySection\Image\Tasks\CreateImageInAlbumTask;
use App\Containers\GallerySection\Image\Tasks\SaveUploadedImageTask;
use App\Containers\GallerySection\Image\UI\API\Requests\UploadImageFromDeviceRequest;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class UploadImageFromDeviceAction extends UseCaseAction
{
    private const string SOURCE_TYPE = FileSourceEnum::DEVICE->value;

    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_IMAGE;

    public function __construct(
        private readonly SaveUploadedImageTask $saveUploadedImageTask,
        private readonly CreateImageInAlbumTask $createImageInAlbumTask,
        private readonly CreateAllImageThumbsTask $createAllImageThumbsTask,
        private readonly PathGenerationService $pathGenerationService,
    ) {
        parent::__construct();
    }

    public function handle(Album $album, UploadImageFromDeviceDto $uploadImagesDto): Image
    {
        return DB::transaction(function () use ($album, $uploadImagesDto) {
            $file = $uploadImagesDto->file;
            $uuid = Uuid::uuid4()->toString();

            $albumPath = $this->pathGenerationService->getAlbumFolderPath((string) $uploadImagesDto->user_id, (string) $album->id);
            $basePath = $albumPath . '/' . $uuid;
            $filePath = $this->saveUploadedImageTask->run($file, $basePath);

            $imageStrategy = ImageSourceFactory::create($filePath, self::SOURCE_TYPE);

            try {
                $this->createAllImageThumbsTask->run($imageStrategy, $albumPath, $uuid);
            } catch (\Throwable $exception) {
                Log::warning("Unable to create thumbnails({$uuid}): " . $exception->getMessage());
            }

            $createImageDto = CreateImageDto::from([
                'id' => $uuid,
                'user_id' => $uploadImagesDto->user_id,
                'extension' => ImageMimeEnum::canonicalize($file->getClientOriginalExtension())
                    ?? strtolower($file->getClientOriginalExtension()),
                'width' => $imageStrategy->getImage()->width(),
                'height' => $imageStrategy->getImage()->height(),
                'source' => self::SOURCE_TYPE,
            ]);

            $image = $this->createImageInAlbumTask->run($album, $createImageDto);

            if (!$album->isUploadStagingAlbum()) {
                $this->recordUseCase($image);
            }

            return $image;
        });
    }

    public function asController(Album $album, UploadImageFromDeviceRequest $request): JsonResponse
    {
        $uploadImagesDto = UploadImageFromDeviceDto::from($request->validated());
        $uploadImagesDto->user_id = (int) auth()->id();

        $result = $this->handle($album, $uploadImagesDto);

        return fractal($result, new ImageTransformer())
            ->withResourceName(ContainerAliasEnum::GALLERY_IMAGE->value)
            ->addMeta(['message' => 'Image successfully uploaded!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
