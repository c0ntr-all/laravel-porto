<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Media\Data\DTO\CreateMediaDto;
use App\Containers\GallerySection\Media\Data\DTO\UploadMediaFromWindowsDto;
use App\Containers\GallerySection\Media\Enums\ImageThumbTypeEnum;
use App\Containers\GallerySection\Media\Enums\MediaSourceEnum;
use App\Containers\GallerySection\Media\Factories\ImageSourceFactory;
use App\Containers\GallerySection\Media\Services\PathGenerationService;
use App\Containers\GallerySection\Media\Tasks\CreateAllImageThumbsTask;
use App\Containers\GallerySection\Media\Tasks\CreateMediaInAlbumTask;
use App\Containers\GallerySection\Media\Tasks\DefineMediaTypeTask;
use App\Containers\GallerySection\Media\UI\API\Requests\UploadMediaFromWindowsRequest;
use App\Containers\GallerySection\Media\UI\API\Transformers\MediaTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadMediaFromWindowsAction
{
    use AsAction;

    private const string SOURCE_TYPE = MediaSourceEnum::WINDOWS->value;

    public function __construct(
        private readonly CreateMediaInAlbumTask $createMediaInAlbumTask,
        private readonly DefineMediaTypeTask    $defineMediaTypeTask,
        private readonly CreateAllImageThumbsTask $createAllImageThumbsTask,
        private readonly PathGenerationService $pathGenerationService
    )
    {
    }

    public function handle(Album $album, UploadMediaFromWindowsDto $uploadMediaDto): Collection
    {
        $result = collect();
        foreach($uploadMediaDto->paths as $filePath) {
            $imageStrategy = ImageSourceFactory::create($filePath, self::SOURCE_TYPE);
            $albumPath = $this->pathGenerationService->getAlbumFolderPath($uploadMediaDto->user_id, $album->id);
            $thumbnails = $this->createAllImageThumbsTask->run($imageStrategy, $albumPath);

            $createMediaDto = CreateMediaDto::from([
                'user_id' => $uploadMediaDto->user_id,
                'type' => $this->defineMediaTypeTask->run($filePath),
                'original_path' => $imageStrategy->getOriginalPath(),
                'list_thumb_path' => $thumbnails[ImageThumbTypeEnum::LIST->value],
                'preview_thumb_path' => $thumbnails[ImageThumbTypeEnum::PREVIEW->value],
                'width' => $imageStrategy->getImage()->width(),
                'height' => $imageStrategy->getImage()->height(),
                'source' => self::SOURCE_TYPE,
            ]);

            $savedMedia = $this->createMediaInAlbumTask->run($album, $createMediaDto);

            $result->push($savedMedia);
        }

        return $result;
    }

    public function asController(Album $album, UploadMediaFromWindowsRequest $request): JsonResponse
    {
        $dto = UploadMediaFromWindowsDto::from($request->validated());
        $dto->user_id = (string)auth()->user()->id;

        $media = $this->handle($album, $dto);

        return fractal($media, new MediaTransformer())
            ->withResourceName('media')
            ->addMeta('Media successfully uploaded!')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
