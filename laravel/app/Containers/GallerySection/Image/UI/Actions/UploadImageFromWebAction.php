<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUseCaseTask;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Data\DTO\CreateImageDto;
use App\Containers\GallerySection\Image\Data\DTO\UploadImageFromWebDto;
use App\Containers\GallerySection\Image\Factories\ImageSourceFactory;
use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Image\Services\PathGenerationService;
use App\Containers\GallerySection\Image\Tasks\CreateAllImageThumbsTask;
use App\Containers\GallerySection\Image\Tasks\CreateImageInAlbumTask;
use App\Containers\GallerySection\Image\UI\API\Requests\UploadImageFromWebRequest;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class UploadImageFromWebAction extends UseCaseAction
{
    private const string SOURCE_TYPE = FileSourceEnum::WEB->value;

    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_IMAGE;

    public function __construct(
        private readonly CreateAllImageThumbsTask $createAllImageThumbsTask,
        private readonly PathGenerationService $pathGenerationService,
        private readonly CreateImageInAlbumTask $createImageInAlbumTask,
        private readonly CreateActivityUseCaseTask $createActivityUseCaseTask,
    ) {
        parent::__construct();
    }

    public function handle(Album $album, UploadImageFromWebDto $uploadImageFromWebDto): Image
    {
        return DB::transaction(function () use ($album, $uploadImageFromWebDto) {
            $uuid = Uuid::uuid4()->toString();
            $imageStrategy = ImageSourceFactory::create($uploadImageFromWebDto->link, self::SOURCE_TYPE);
            $albumPath = $this->pathGenerationService->getAlbumFolderPath(
                (string) $uploadImageFromWebDto->user_id,
                (string) $album->id,
            );

            try {
                $this->createAllImageThumbsTask->run($imageStrategy, $albumPath, $uuid);
            } catch (\Throwable $exception) {
                Log::warning("Unable to create thumbnails({$uuid}): " . $exception->getMessage());
            }

            $createImageDto = CreateImageDto::from([
                'id' => $uuid,
                'user_id' => $uploadImageFromWebDto->user_id,
                'extension' => $imageStrategy->getExtension(),
                'external_url' => $uploadImageFromWebDto->link,
                'width' => $imageStrategy->getImage()->width(),
                'height' => $imageStrategy->getImage()->height(),
                'source' => self::SOURCE_TYPE,
            ]);

            $image = $this->createImageInAlbumTask->run($album, $createImageDto);

            DB::afterCommit(function () use ($image) {
                $this->createActivityUseCaseTask->run($image, $this->eventTypesEnum->value);
            });

            return $image;
        });
    }

    public function asController(Album $album, UploadImageFromWebRequest $request): JsonResponse
    {
        $dto = UploadImageFromWebDto::from($request->validated());
        $dto->user_id = (int) auth()->id();

        $image = $this->handle($album, $dto);

        return fractal($image, new ImageTransformer())
            ->withResourceName(ContainerAliasEnum::GALLERY_IMAGE->value)
            ->addMeta(['message' => 'Image successfully uploaded!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
