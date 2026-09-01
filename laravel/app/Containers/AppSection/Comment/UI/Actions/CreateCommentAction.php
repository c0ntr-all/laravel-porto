<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\UI\Actions;

use App\Containers\AppSection\Comment\Data\DTO\CommentCreateData;
use App\Containers\AppSection\Comment\Models\Comment;
use App\Containers\AppSection\Comment\Tasks\CreateCommentTask;
use App\Containers\AppSection\Comment\UI\API\Requests\CreateRequest;
use App\Containers\AppSection\Comment\UI\API\Transformers\CommentTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Exceptions\CreateResourceFailedException;
use Illuminate\Http\JsonResponse;
use App\Ship\Parents\Actions\BaseAction;

class CreateCommentAction extends BaseAction
{

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
        $dto = CommentCreateData::from([
            ...$request->validated(),
            'user_id' => (int) auth()->id(),
        ]);
        $comment = $this->handle($dto);

        return fractal($comment, new CommentTransformer())
            ->withResourceName(ContainerAliasEnum::COMMENTS->value)
            ->parseIncludes('user')
            ->addMeta(['message' => 'Comment created successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
