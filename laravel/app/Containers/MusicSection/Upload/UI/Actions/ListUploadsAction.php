<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\Actions;

use App\Containers\MusicSection\Upload\Tasks\ListUploadsTask;
use App\Containers\MusicSection\Upload\UI\API\Requests\IndexRequest;
use App\Containers\MusicSection\Upload\UI\API\Transformers\UploadTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;

class ListUploadsAction extends BaseAction
{
    public function __construct(
        private readonly ListUploadsTask $listUploadsTask
    ) {
    }

    public function handle(): CursorPaginator
    {
        return $this->listUploadsTask->run();
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        return fractal($this->handle(), new UploadTransformer())
            ->withResourceName('uploads')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
