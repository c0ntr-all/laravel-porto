<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\UI\Actions;

use App\Containers\AppSection\Comment\Data\DTO\CommentListDto;
use App\Containers\AppSection\Comment\Tasks\ListCommentsTask;
use App\Containers\AppSection\Comment\UI\API\Requests\ListCommentsRequest;
use App\Containers\AppSection\Comment\UI\API\Transformers\CommentTransformer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class ListCommentsAction
{
    use AsAction;

    public function __construct(
        private readonly ListCommentsTask $listCommentsTask
    )
    {
    }

    public function handle(CommentListDto $dto): Collection
    {
        return $this->listCommentsTask->run($dto);
    }

    public function asController(ListCommentsRequest $request): JsonResponse
    {
        $dto = CommentListDto::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $comments = $this->handle($dto);

        return fractal($comments, new CommentTransformer())
            ->parseIncludes(['user'])
            ->withResourceName('comments')
            ->addMeta(['count' => $comments->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
