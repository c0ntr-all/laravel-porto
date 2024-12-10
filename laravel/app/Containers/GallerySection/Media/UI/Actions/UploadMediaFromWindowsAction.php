<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Media\Data\DTO\UploadMediaFromWindowsDto;
use App\Containers\GallerySection\Media\Tasks\UploadMediaWindowsTask;
use App\Containers\GallerySection\Media\UI\API\Requests\UploadMediaFromWindowsRequest;
use App\Containers\GallerySection\Media\UI\API\Transformers\MediaTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadMediaFromWindowsAction
{
    use AsAction;

    public function __construct(
        private readonly UploadMediaWindowsTask $uploadMediaWindowsTask
    )
    {
    }

    public function handle(Album $album, UploadMediaFromWindowsDto $uploadMediaDto): Collection
    {
        $result = collect();
        foreach($uploadMediaDto->paths as $filePath) {
            $savedMedia = $this->uploadMediaWindowsTask->run($album, $filePath, $uploadMediaDto->user_id);

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
