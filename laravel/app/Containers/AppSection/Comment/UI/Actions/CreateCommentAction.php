<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\UI\Actions;

use App\Containers\AppSection\Comment\Data\DTO\CommentCreateData;
use App\Containers\AppSection\Comment\Models\Comment;
use App\Containers\AppSection\Comment\Tasks\CreateCommentTask;
use App\Containers\AppSection\Comment\UI\API\Requests\CreateRequest;
use App\Containers\AppSection\Comment\UI\API\Transformers\CommentTransformer;
use App\Ship\Exceptions\CreateResourceFailedException;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCommentAction
{
    use AsAction;

    public function __construct(
        private readonly CreateCommentTask $commentCreateTask
    )
    {
    }

    /**
     * @throws CreateResourceFailedException
     */
    public function handle(CommentCreateData $dto): Comment
    {
        return $this->commentCreateTask->run($dto);
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $dto = CommentCreateData::from($request->toArray());
        $dto->user_id = auth()->user()->id;
        $comment = $this->handle($dto);

        return fractal($comment, new CommentTransformer())
            ->withResourceName('comments')
            ->parseIncludes('user')
            ->addMeta(['message' => 'Comment created successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
