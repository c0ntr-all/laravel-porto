<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\UI\Actions;

use App\Containers\AppSection\Comment\Data\DTO\CommentCreateData;
use App\Containers\AppSection\Comment\Models\Comment;
use App\Containers\AppSection\Comment\Tasks\CreateCommentTask;
use App\Containers\AppSection\Comment\UI\API\Requests\CreateRequest;
use App\Containers\AppSection\Comment\UI\API\Transformers\CommentTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Exceptions\CreateResourceFailedException;
use Illuminate\Http\JsonResponse;
use App\Ship\Parents\Actions\UseCaseAction;

class CreateCommentAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::COMMENTS;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;

    public function __construct(
        private readonly CreateCommentTask $commentCreateTask
    )
    {
        parent::__construct();
    }

    /**
     * @throws CreateResourceFailedException
     */
    public function handle(CommentCreateData $dto): Comment
    {
        $comment = $this->commentCreateTask->run($dto);

        $this->recordUseCase($comment);

        return $comment;
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
