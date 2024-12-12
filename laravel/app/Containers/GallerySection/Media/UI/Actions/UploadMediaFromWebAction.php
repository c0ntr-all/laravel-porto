<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Media\Data\DTO\CreateMediaDto;
use App\Containers\GallerySection\Media\Data\DTO\UploadMediaFromWebDto;
use App\Containers\GallerySection\Media\Enums\ImageThumbTypeEnum;
use App\Containers\GallerySection\Media\Enums\MediaSourceEnum;
use App\Containers\GallerySection\Media\Factories\ImageSourceFactory;
use App\Containers\GallerySection\Media\Models\Media;
use App\Containers\GallerySection\Media\Services\PathGenerationService;
use App\Containers\GallerySection\Media\Tasks\CreateAllImageThumbsTask;
use App\Containers\GallerySection\Media\Tasks\CreateMediaInAlbumTask;
use App\Containers\GallerySection\Media\UI\API\Requests\UploadMediaFromWebRequest;
use App\Containers\GallerySection\Media\UI\API\Transformers\MediaTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadMediaFromWebAction
{
    use AsAction;

    private const string SOURCE_TYPE = MediaSourceEnum::WEB->value;

    public function __construct(
        private readonly CreateAllImageThumbsTask $createAllImageThumbsTask,
        private readonly PathGenerationService    $pathGenerationService,
        private readonly CreateMediaInAlbumTask   $createMediaInAlbumTask,
    )
    {
    }

    public function handle(Album $album, UploadMediaFromWebDto $uploadMediaFromWebDto): Media
    {
        $imageStrategy = ImageSourceFactory::create($uploadMediaFromWebDto->link, self::SOURCE_TYPE);
        $albumPath = $this->pathGenerationService->getAlbumFolderPath($uploadMediaFromWebDto->user_id, $album->id);
        $thumbnails = $this->createAllImageThumbsTask->run($imageStrategy, $albumPath);

        $createMediaDto = CreateMediaDto::from([
            'user_id' => $uploadMediaFromWebDto->user_id,
            'type' => 'image',
            'original_path' => $uploadMediaFromWebDto->link,
            'list_thumb_path' => $thumbnails[ImageThumbTypeEnum::LIST->value],
            'preview_thumb_path' => $thumbnails[ImageThumbTypeEnum::PREVIEW->value],
            'width' => $imageStrategy->getImage()->width(),
            'height' => $imageStrategy->getImage()->height(),
            'source' => self::SOURCE_TYPE
        ]);

        return $this->createMediaInAlbumTask->run($album, $createMediaDto);
    }

    public function asController(Album $album, UploadMediaFromWebRequest $request): JsonResponse
    {
        $dto = UploadMediaFromWebDto::from($request->validated());
        $dto->user_id = (string)auth()->user()->id;

        $media = $this->handle($album, $dto);

        return fractal($media, new MediaTransformer())
            ->withResourceName('media')
            ->addMeta('Media successfully uploaded!')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
