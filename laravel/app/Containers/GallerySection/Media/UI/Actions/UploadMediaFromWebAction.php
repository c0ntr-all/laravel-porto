<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Media\Data\DTO\UploadMediaFromWebDto;
use App\Containers\GallerySection\Media\Models\Media;
use App\Containers\GallerySection\Media\Tasks\UploadMediaWebTask;
use App\Containers\GallerySection\Media\UI\API\Requests\UploadMediaFromWebRequest;
use App\Containers\GallerySection\Media\UI\API\Transformers\MediaTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadMediaFromWebAction
{
    use AsAction;

    public function __construct(
        private readonly UploadMediaWebTask $uploadMediaWebTask
    )
    {
    }

    public function handle(Album $album, UploadMediaFromWebDto $dto): Media
    {
        return $this->uploadMediaWebTask->run($album, $dto);
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
