<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\UI\Actions;

use App\Containers\MusicSection\History\Data\DTO\UpdateHistoryDto;
use App\Containers\MusicSection\History\Models\History;
use App\Containers\MusicSection\History\Tasks\UpdateHistoryTask;
use App\Containers\MusicSection\History\UI\API\Requests\UpdateRequest;
use App\Containers\MusicSection\History\UI\API\Transformers\HistoryTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UpdateHistoryAction extends BaseAction
{
    public function __construct(
        private readonly UpdateHistoryTask $updateHistoryTask
    )
    {
    }

    public function handle(History $history, UpdateHistoryDto $dto): History
    {
        return $this->updateHistoryTask->run($history, $dto);
    }

    public function asController(History $history, UpdateRequest $request): JsonResponse
    {
        $history = $this->handle($history, UpdateHistoryDto::from($request->validated()))->load('track');

        return fractal($history, new HistoryTransformer())
            ->withResourceName('history')
            ->parseIncludes(['track'])
            ->addMeta(['message' => 'History record updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
