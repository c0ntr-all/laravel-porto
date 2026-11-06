<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\UI\Actions;

use App\Containers\AppSection\Comment\Models\Comment;
use App\Containers\AppSection\Comment\Tasks\DeleteCommentTask;
use App\Containers\AppSection\Comment\UI\API\Requests\DeleteRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DeleteCommentAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::COMMENTS;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;

    public function __construct(
        private readonly DeleteCommentTask $deleteCommentTask
    )
    {
        parent::__construct();
    }

    public function handle(Comment $comment, int $userId): bool
    {
        if ((int) $comment->user_id !== $userId) {
            throw new AccessDeniedHttpException('You can change only your own comments.');
        }

        return DB::transaction(function () use ($comment): bool {
            $this->recordUseCase($comment);

            return $this->deleteCommentTask->run($comment);
        });
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
