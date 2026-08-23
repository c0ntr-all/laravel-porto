<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\UI\Actions;

use App\Containers\MusicSection\History\Tasks\ListHistoryTask;
use App\Containers\MusicSection\History\UI\API\Requests\IndexRequest;
use App\Containers\MusicSection\History\UI\API\Transformers\HistoryTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;

class ListHistoryAction extends BaseAction
{
    public function __construct(
        private readonly ListHistoryTask $listHistoryTask
    )
    {
    }

    public function handle(int $userId): CursorPaginator
    {
        return $this->listHistoryTask->run($userId);
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $history = $this->handle((int) auth()->id());

        return fractal($history, new HistoryTransformer())
            ->withResourceName('history')
            ->parseIncludes(['track'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
