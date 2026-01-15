<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Data\DTO\CreateImageDto;
use App\Containers\GallerySection\Image\Data\DTO\UploadImageFromWindowsDto;
use App\Containers\GallerySection\Image\Enums\ImageThumbTypeEnum;
use App\Containers\GallerySection\Image\Factories\ImageSourceFactory;
use App\Containers\GallerySection\Image\Services\PathGenerationService;
use App\Containers\GallerySection\Image\Tasks\CreateAllImageThumbsTask;
use App\Containers\GallerySection\Image\Tasks\CreateImageInAlbumTask;
use App\Containers\GallerySection\Image\UI\API\Requests\UploadImagesFromWindowsRequest;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use App\Ship\Enums\FileSourceEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadImagesFromWindowsAction
{
    use AsAction;

    private const string SOURCE_TYPE = FileSourceEnum::WINDOWS->value;

    public function __construct(
        private readonly CreateImageInAlbumTask   $createImageInAlbumTask,
        private readonly CreateAllImageThumbsTask $createAllImageThumbsTask,
        private readonly PathGenerationService    $pathGenerationService
    )
    {
    }

    public function handle(Album $album, UploadImageFromWindowsDto $uploadImageDto): Collection
    {
        $result = collect();
        foreach($uploadImageDto->paths as $filePath) {
            $imageStrategy = ImageSourceFactory::create($filePath, self::SOURCE_TYPE);
            $albumPath = $this->pathGenerationService->getAlbumFolderPath($uploadImageDto->user_id, $album->id);
            $thumbnails = $this->createAllImageThumbsTask->run($imageStrategy, $albumPath);

            $createImageDto = CreateImageDto::from([
                'user_id' => $uploadImageDto->user_id,
                'original_path' => $imageStrategy->getOriginalPath(),
                'list_thumb_path' => $thumbnails[ImageThumbTypeEnum::LIST->value],
                'preview_thumb_path' => $thumbnails[ImageThumbTypeEnum::PREVIEW->value],
                'width' => $imageStrategy->getImage()->width(),
                'height' => $imageStrategy->getImage()->height(),
                'source' => self::SOURCE_TYPE,
            ]);

            $savedImage = $this->createImageInAlbumTask->run($album, $createImageDto);

            $result->push($savedImage);
        }

        return $result;
    }

    public function asController(Album $album, UploadImagesFromWindowsRequest $request): JsonResponse
    {
        $dto = UploadImageFromWindowsDto::from($request->validated());
        $dto->user_id = (string)auth()->user()->id;

        $images = $this->handle($album, $dto);

        return fractal($images, new ImageTransformer())
            ->withResourceName('images')
            ->addMeta('Image successfully uploaded!')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
