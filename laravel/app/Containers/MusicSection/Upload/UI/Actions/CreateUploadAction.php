<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\Actions;

use App\Containers\MusicSection\Upload\Data\DTO\CreateUploadDto;
use App\Containers\MusicSection\Upload\Helpers\PathHelper;
use App\Containers\MusicSection\Upload\Jobs\ImportArtistMusicJob;
use App\Containers\MusicSection\Upload\Models\MusicUpload;
use App\Containers\MusicSection\Upload\Tasks\CreateUploadSessionTask;
use App\Containers\MusicSection\Upload\UI\API\Requests\CreateRequest;
use App\Containers\MusicSection\Upload\UI\API\Transformers\UploadTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Throwable;

class CreateUploadAction extends BaseAction
{
    public function __construct(
        private readonly CreateUploadSessionTask $createUploadSessionTask,
    ) {
    }

    public function handle(CreateUploadDto $dto): MusicUpload
    {
        $upload = $this->createUploadSessionTask->run($dto);

        try {
            ImportArtistMusicJob::dispatchSync($upload);
        } catch (Throwable) {
            // Session already contains failed status and error_message.
        }

        return $upload->fresh(['artists', 'albums.artists', 'tracks.album', 'tracks.artist']) ?? $upload;
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $upload = $this->handle(CreateUploadDto::from([
            'user_id' => (int) auth()->id(),
            'path' => PathHelper::normalizeWindows($request->validated('path')),
        ]));

        return fractal($upload, new UploadTransformer())
            ->withResourceName('uploads')
            ->parseIncludes(['artists', 'albums.artists', 'tracks'])
            ->addMeta(['message' => 'Upload session finished with status ' . $upload->status->value . '.'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
