<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Album\UI\API\Requests\UploadMediaRequest;
use App\Containers\GallerySection\Media\Data\DTO\UploadMediaDto;
use App\Containers\GallerySection\Media\Tasks\UploadMediaToAlbumTask;
use App\Containers\GallerySection\Media\UI\API\Transformers\MediaTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadMediaToAlbumAction
{
    use AsAction;

    public function __construct(
        private readonly UploadMediaToAlbumTask $uploadMediaToAlbumTask
    )
    {
    }

    public function handle(Album $album, UploadMediaDto $dto): Collection
    {
        return $this->uploadMediaToAlbumTask->run($album, $dto);
    }

    public function asController(Album $album, UploadMediaRequest $request): JsonResponse
    {
        $dto = UploadMediaDto::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $media = $this->handle($album, $dto);

        return fractal($media, new MediaTransformer())
            ->withResourceName('media')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
