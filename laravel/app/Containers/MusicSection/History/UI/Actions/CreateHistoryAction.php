<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\UI\Actions;

use App\Containers\MusicSection\History\Data\DTO\CreateHistoryDto;
use App\Containers\MusicSection\History\Models\History;
use App\Containers\MusicSection\History\Tasks\CreateHistoryTask;
use App\Containers\MusicSection\History\UI\API\Requests\CreateRequest;
use App\Containers\MusicSection\History\UI\API\Transformers\HistoryTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class CreateHistoryAction extends BaseAction
{
    public function __construct(
        private readonly CreateHistoryTask $createHistoryTask
    )
    {
    }

    public function handle(CreateHistoryDto $dto): History
    {
        return $this->createHistoryTask->run($dto);
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $dto = CreateHistoryDto::from([
            ...$request->validated(),
            'user_id' => (int) auth()->id(),
        ]);

        $history = $this->handle($dto)->load('track');

        return fractal($history, new HistoryTransformer())
            ->withResourceName('history')
            ->parseIncludes(['track'])
            ->addMeta(['message' => 'History record created successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
