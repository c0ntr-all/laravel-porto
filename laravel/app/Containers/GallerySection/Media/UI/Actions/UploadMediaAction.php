<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Media\Data\DTO\CreateMediaDto;
use App\Containers\GallerySection\Media\Data\DTO\UploadMediaDto;
use App\Containers\GallerySection\Media\Enums\ImageThumbTypeEnum;
use App\Containers\GallerySection\Media\Enums\MediaSourceEnum;
use App\Containers\GallerySection\Media\Factories\ImageSourceFactory;
use App\Containers\GallerySection\Media\Models\Media;
use App\Containers\GallerySection\Media\Services\PathGenerationService;
use App\Containers\GallerySection\Media\Tasks\CreateAllImageThumbsTask;
use App\Containers\GallerySection\Media\Tasks\CreateMediaInAlbumTask;
use App\Containers\GallerySection\Media\Tasks\UploadMediaTask;
use App\Containers\GallerySection\Media\UI\API\Requests\UploadMediaRequest;
use App\Containers\GallerySection\Media\UI\API\Transformers\MediaTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadMediaAction
{
    use AsAction;

    private const string SOURCE_TYPE = MediaSourceEnum::DEVICE->value;

    public function __construct(
        private readonly UploadMediaTask $uploadMediaTask,
        private readonly CreateMediaInAlbumTask $createMediaInAlbumTask,
        private readonly CreateAllImageThumbsTask $createAllImageThumbsTask,
        private readonly PathGenerationService $pathGenerationService
    )
    {
    }

    public function handle(Album $album, UploadMediaDto $uploadMediaDto): Media
    {
        $file = $uploadMediaDto->file;
        $albumPath = $this->pathGenerationService->getAlbumFolderPath($uploadMediaDto->user_id, $album->id);
        $filePath = $this->uploadMediaTask->run($file, $albumPath);

        $imageStrategy = ImageSourceFactory::create($filePath, self::SOURCE_TYPE);

        $thumbnails = $this->createAllImageThumbsTask->run($imageStrategy, $albumPath);

        $createMediaDto = CreateMediaDto::from([
            'user_id' => $uploadMediaDto->user_id,
            'type' => 'image',
            'original_path' => $filePath,
            'list_thumb_path' => $thumbnails[ImageThumbTypeEnum::LIST->value],
            'preview_thumb_path' => $thumbnails[ImageThumbTypeEnum::PREVIEW->value],
            'width' => $imageStrategy->getImage()->getWidth(),
            'height' => $imageStrategy->getImage()->getHeight(),
            'source' => self::SOURCE_TYPE
        ]);

        return $this->createMediaInAlbumTask->run($album, $createMediaDto);
    }

    public function asController(Album $album, UploadMediaRequest $request): JsonResponse
    {
        $uploadMediaDto = UploadMediaDto::from($request->validated());
        $uploadMediaDto->user_id = (string)auth()->user()->id;

        $result = $this->handle($album, $uploadMediaDto);

        return fractal($result, new MediaTransformer())
            ->withResourceName('media')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
