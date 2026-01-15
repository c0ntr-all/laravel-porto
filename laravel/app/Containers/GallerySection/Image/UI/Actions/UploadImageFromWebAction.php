<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Data\DTO\CreateImageDto;
use App\Containers\GallerySection\Image\Data\DTO\UploadImageFromWebDto;
use App\Containers\GallerySection\Image\Enums\ImageThumbTypeEnum;
use App\Containers\GallerySection\Image\Factories\ImageSourceFactory;
use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Image\Services\PathGenerationService;
use App\Containers\GallerySection\Image\Tasks\CreateAllImageThumbsTask;
use App\Containers\GallerySection\Image\Tasks\CreateImageInAlbumTask;
use App\Containers\GallerySection\Image\UI\API\Requests\UploadImageFromWebRequest;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use App\Ship\Enums\FileSourceEnum;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadImageFromWebAction
{
    use AsAction;

    private const string SOURCE_TYPE = FileSourceEnum::WEB->value;

    public function __construct(
        private readonly CreateAllImageThumbsTask $createAllImageThumbsTask,
        private readonly PathGenerationService    $pathGenerationService,
        private readonly CreateImageInAlbumTask   $createImageInAlbumTask,
    )
    {
    }

    public function handle(Album $album, UploadImageFromWebDto $uploadImageFromWebDto): Image
    {
        $imageStrategy = ImageSourceFactory::create($uploadImageFromWebDto->link, self::SOURCE_TYPE);
        $albumPath = $this->pathGenerationService->getAlbumFolderPath($uploadImageFromWebDto->user_id, $album->id);
        $thumbnails = $this->createAllImageThumbsTask->run($imageStrategy, $albumPath);

        $createImageDto = CreateImageDto::from([
            'user_id' => $uploadImageFromWebDto->user_id,
            'original_path' => $uploadImageFromWebDto->link,
            'list_thumb_path' => $thumbnails[ImageThumbTypeEnum::LIST->value],
            'preview_thumb_path' => $thumbnails[ImageThumbTypeEnum::PREVIEW->value],
            'width' => $imageStrategy->getImage()->width(),
            'height' => $imageStrategy->getImage()->height(),
            'source' => self::SOURCE_TYPE
        ]);

        return $this->createImageInAlbumTask->run($album, $createImageDto);
    }

    public function asController(Album $album, UploadImageFromWebRequest $request): JsonResponse
    {
        $dto = UploadImageFromWebDto::from($request->validated());
        $dto->user_id = (string)auth()->user()->id;

        $image = $this->handle($album, $dto);

        return fractal($image, new ImageTransformer())
            ->withResourceName('images')
            ->addMeta('Image successfully uploaded!')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
