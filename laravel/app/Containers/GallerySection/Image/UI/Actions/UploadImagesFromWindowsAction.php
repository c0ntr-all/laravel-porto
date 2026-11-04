<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Data\DTO\CreateImageDto;
use App\Containers\GallerySection\Image\Data\DTO\UploadImageFromWindowsDto;
use App\Containers\GallerySection\Image\Enums\ImageMimeEnum;
use App\Containers\GallerySection\Image\Jobs\GenerateGalleryImageThumbJob;
use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Image\Tasks\CreateImageInAlbumTask;
use App\Containers\GallerySection\Image\Tasks\ReadImageFileFactsTask;
use App\Containers\GallerySection\Image\UI\API\Requests\UploadImagesFromWindowsRequest;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Helpers\UuidV7;
use App\Ship\Helpers\WindowsPathHelper;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UploadImagesFromWindowsAction extends UseCaseAction
{
    private const string SOURCE_TYPE = FileSourceEnum::WINDOWS->value;

    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::GALLERY_IMAGE;

    public function __construct(
        private readonly CreateImageInAlbumTask $createImageInAlbumTask,
        private readonly ReadImageFileFactsTask $readImageFileFactsTask,
    ) {
        parent::__construct();
    }

    public function handle(Album $album, UploadImageFromWindowsDto $uploadImageDto): Collection
    {
        return DB::transaction(function () use ($album, $uploadImageDto) {
            $result = collect();
            $created = collect();
            $disk = (string) config('image.windows.disk', 'windows_f');
            $root = (string) config('image.windows.root_folder', config('app.windows_images_root_folder'));

            foreach ($uploadImageDto->paths as $filePath) {
                $windowsPath = WindowsPathHelper::normalizeWindows($filePath);
                $existing = $this->findCanonical((int) $uploadImageDto->user_id, $windowsPath);

                if ($existing !== null) {
                    $existing->albums()->syncWithoutDetaching([(int) $album->id]);
                    $result->push($existing);
                    continue;
                }

                $absolutePath = WindowsPathHelper::assertReadableFile($windowsPath, $disk, $root);
                $facts = $this->readImageFileFactsTask->run($absolutePath);
                $extension = ImageMimeEnum::canonicalize(
                    (string) pathinfo(str_replace('\\', '/', $windowsPath), PATHINFO_EXTENSION)
                );

                $image = $this->createImageInAlbumTask->run($album, CreateImageDto::from([
                    'uuid' => UuidV7::generate(),
                    'user_id' => $uploadImageDto->user_id,
                    'extension' => $extension ?? 'jpg',
                    'external_url' => $windowsPath,
                    'width' => $facts['width'],
                    'height' => $facts['height'],
                    'source' => self::SOURCE_TYPE,
                    'thumb_status' => 'pending',
                    'file_size' => $facts['file_size'],
                    'file_mtime' => $facts['file_mtime'],
                    'taken_at' => $facts['taken_at'],
                ]));

                $result->push($image);
                $created->push($image);

                if (!$album->isUploadStagingAlbum()) {
                    $this->recordUseCase($image);
                }
            }

            DB::afterCommit(function () use ($created) {
                foreach ($created as $image) {
                    GenerateGalleryImageThumbJob::dispatch($image->id);
                }
            });

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

    private function findCanonical(int $userId, string $windowsPath): ?Image
    {
        return Image::query()
            ->where('user_id', $userId)
            ->where('source', self::SOURCE_TYPE)
            ->where('external_url', $windowsPath)
            ->orderByRaw('saved_from_id IS NULL DESC')
            ->orderBy('id')
            ->first();
    }
}
