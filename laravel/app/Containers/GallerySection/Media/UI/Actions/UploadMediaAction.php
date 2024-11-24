<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Media\Data\DTO\CreateMediaDto;
use App\Containers\GallerySection\Media\Data\DTO\UploadMediaDto;
use App\Containers\GallerySection\Media\Tasks\CreateMediaInAlbumTask;
use App\Containers\GallerySection\Media\Tasks\UploadMediaTask;
use App\Containers\GallerySection\Media\UI\API\Requests\UploadMediaRequest;
use App\Containers\GallerySection\Media\UI\API\Transformers\MediaTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadMediaAction
{
    use AsAction;

    public function __construct(
        private readonly UploadMediaTask $uploadMediaTask,
        private readonly CreateMediaInAlbumTask $createMediaInAlbumTask
    )
    {
    }

    public function handle(Album $album, UploadMediaDto $uploadMediaDto): array
    {
        $files = $uploadMediaDto->files;
        $result = [];

        foreach ($files as $file) {
            $filePath = $this->uploadMediaTask->run($file, $album->id);

            $createMediaDto = CreateMediaDto::from([
                'user_id' => $uploadMediaDto->user_id,
                'type' => 'image',
                'path' => $filePath,
                'source' => 'device'
            ]);

            $result[] = $this->createMediaInAlbumTask->run($album, $createMediaDto);
        }

        return $result;
    }

    public function asController(Album $album, UploadMediaRequest $request): JsonResponse
    {
        $uploadMediaDto = UploadMediaDto::from($request->validated());
        $uploadMediaDto->user_id = auth()->user()->id;

        $result = $this->handle($album, $uploadMediaDto);

        return fractal($result, new MediaTransformer())
            ->withResourceName('media')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
