<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\UI\Actions;

use App\Containers\AppSection\Comment\Data\DTO\CommentUpdateData;
use App\Containers\AppSection\Comment\Models\Comment;
use App\Containers\AppSection\Comment\Tasks\UpdateCommentTask;
use App\Containers\AppSection\Comment\UI\API\Requests\UpdateRequest;
use App\Containers\AppSection\Comment\UI\API\Transformers\CommentTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use Illuminate\Http\JsonResponse;
use App\Ship\Parents\Actions\BaseAction;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UpdateCommentAction extends BaseAction
{
    public function __construct(
        private readonly UpdateCommentTask $updateCommentTask
    )
    {
    }

    public function handle(Comment $comment, CommentUpdateData $dto, int $userId): Comment
    {
        if ((int) $comment->user_id !== $userId) {
            throw new AccessDeniedHttpException('You can change only your own comments.');
        }

        return $this->updateCommentTask->run($comment, $dto);
    }

    public function asController(Comment $comment, UpdateRequest $request): JsonResponse
    {
        $dto = CommentUpdateData::from($request->validated());
        $comment = $this->handle($comment, $dto, (int) auth()->id());

        return fractal($comment, new CommentTransformer())
            ->withResourceName(ContainerAliasEnum::COMMENTS->value)
            ->parseIncludes('user')
            ->addMeta(['message' => 'Comment updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
