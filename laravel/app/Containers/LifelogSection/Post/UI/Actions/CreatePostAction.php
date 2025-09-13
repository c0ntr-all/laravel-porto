<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\Actions;

use App\Containers\LifelogSection\Post\Data\DTO\PostCreateData;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\LifelogSection\Post\Tasks\CreatePostTask;
use App\Containers\LifelogSection\Post\UI\API\Requests\CreateRequest;
use App\Containers\LifelogSection\Post\UI\API\Transformers\PostTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePostAction
{
    use AsAction;

    public function __construct(
        private readonly CreatePostTask $createPostTask
    )
    {
    }

    public function handle(PostCreateData $dto): Post
    {
        return $this->createPostTask->run($dto);
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $dto = PostCreateData::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $task = $this->handle($dto);

        return fractal($task, new PostTransformer())
            ->parseIncludes(['user'])
            ->withResourceName('tasks')
            ->addMeta(['message' => 'New task successfully created!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
