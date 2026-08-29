<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\Actions;

use App\Containers\MusicSection\Upload\Models\MusicUpload;
use App\Containers\MusicSection\Upload\Tasks\ListUploadTracksTask;
use App\Containers\MusicSection\Upload\UI\API\Requests\ListTracksRequest;
use App\Containers\MusicSection\Upload\UI\API\Transformers\UploadTrackTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;

class ListUploadTracksAction extends BaseAction
{
    public function __construct(
        private readonly ListUploadTracksTask $listUploadTracksTask
    ) {
    }

    public function handle(MusicUpload $upload): CursorPaginator
    {
        return $this->listUploadTracksTask->run($upload);
    }

    public function asController(MusicUpload $upload, ListTracksRequest $request): JsonResponse
    {
        return fractal($this->handle($upload), new UploadTrackTransformer())
            ->withResourceName('upload_tracks')
            ->parseIncludes(['album', 'artist'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
