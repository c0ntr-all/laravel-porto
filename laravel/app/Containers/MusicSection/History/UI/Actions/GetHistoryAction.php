<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\UI\Actions;

use App\Containers\MusicSection\History\Models\History;
use App\Containers\MusicSection\History\UI\API\Requests\GetRequest;
use App\Containers\MusicSection\History\UI\API\Transformers\HistoryTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetHistoryAction extends BaseAction
{
    public function handle(History $history): History
    {
        return $history->load('track');
    }

    public function asController(History $history, GetRequest $request): JsonResponse
    {
        $history = $this->handle($history);

        return fractal($history, new HistoryTransformer())
            ->withResourceName('history')
            ->parseIncludes(['track'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
