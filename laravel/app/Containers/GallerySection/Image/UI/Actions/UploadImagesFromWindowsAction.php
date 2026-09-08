<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Data\DTO\CreateImageDto;
use App\Containers\GallerySection\Image\Data\DTO\UploadImageFromWindowsDto;
use App\Containers\GallerySection\Image\Factories\ImageSourceFactory;
use App\Containers\GallerySection\Image\Services\PathGenerationService;
use App\Containers\GallerySection\Image\Tasks\CreateAllImageThumbsTask;
use App\Containers\GallerySection\Image\Tasks\CreateImageInAlbumTask;
use App\Containers\GallerySection\Image\UI\API\Requests\UploadImagesFromWindowsRequest;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Helpers\WindowsPathHelper;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class UploadImagesFromWindowsAction extends UseCaseAction
{
    private const string SOURCE_TYPE = FileSourceEnum::WINDOWS->value;

    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_IMAGE;

    public function __construct(
        private readonly CreateImageInAlbumTask $createImageInAlbumTask,
        private readonly CreateAllImageThumbsTask $createAllImageThumbsTask,
        private readonly PathGenerationService $pathGenerationService,
    ) {
        parent::__construct();
    }

    public function handle(Album $album, UploadImageFromWindowsDto $uploadImageDto): Collection
    {
        return DB::transaction(function () use ($album, $uploadImageDto) {
            $result = collect();

            foreach ($uploadImageDto->paths as $filePath) {
                $uuid = Uuid::uuid4()->toString();
                $windowsPath = WindowsPathHelper::normalizeWindows($filePath);
                $imageStrategy = ImageSourceFactory::create($windowsPath, self::SOURCE_TYPE);
                $albumPath = $this->pathGenerationService->getAlbumFolderPath(
                    (string) $uploadImageDto->user_id,
                    (string) $album->id,
                );

                try {
                    $this->createAllImageThumbsTask->run($imageStrategy, $albumPath, $uuid);
                } catch (\Throwable $exception) {
                    Log::warning("Unable to create thumbnails({$uuid}): " . $exception->getMessage());
                }

                $createImageDto = CreateImageDto::from([
                    'id' => $uuid,
                    'user_id' => $uploadImageDto->user_id,
                    'extension' => $imageStrategy->getExtension(),
                    'external_url' => $windowsPath,
                    'width' => $imageStrategy->getImage()->width(),
                    'height' => $imageStrategy->getImage()->height(),
                    'source' => self::SOURCE_TYPE,
                ]);

                $savedImage = $this->createImageInAlbumTask->run($album, $createImageDto);
                $result->push($savedImage);
            }

            if (!$album->isUploadStagingAlbum()) {
                DB::afterCommit(function () use ($result) {
                    foreach ($result as $image) {
                        $this->recordUseCase($image);
                    }
                });
            }

            return $result;
        });
    }

    public function asController(Album $album, UploadImagesFromWindowsRequest $request): JsonResponse
    {
        $dto = UploadImageFromWindowsDto::from($request->validated());
        $dto->user_id = (int) auth()->id();

        $images = $this->handle($album, $dto);

        return fractal($images, new ImageTransformer())
            ->withResourceName(ContainerAliasEnum::GALLERY_IMAGE->value)
            ->addMeta(['message' => 'Image successfully uploaded!', 'count' => $images->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
