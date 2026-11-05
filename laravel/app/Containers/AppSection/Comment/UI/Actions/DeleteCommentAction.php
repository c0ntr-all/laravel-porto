<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\UI\Actions;

use App\Containers\AppSection\Comment\Models\Comment;
use App\Containers\AppSection\Comment\Tasks\DeleteCommentTask;
use App\Containers\AppSection\Comment\UI\API\Requests\DeleteRequest;
use Illuminate\Http\JsonResponse;
use App\Ship\Parents\Actions\BaseAction;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DeleteCommentAction extends BaseAction
{
    public function __construct(
        private readonly DeleteCommentTask $deleteCommentTask
    )
    {
    }

    public function handle(Comment $comment, int $userId): bool
    {
        if ((int) $comment->user_id !== $userId) {
            throw new AccessDeniedHttpException('You can change only your own comments.');
        }

        return $this->deleteCommentTask->run($comment);
    }

    public function asController(Comment $comment, DeleteRequest $request): JsonResponse
    {
        $this->handle($comment, (int) auth()->id());

        return response()->json([
            'meta' => [
                'message' => 'Comment deleted successfully!',
            ],
        ]);
    }
}
