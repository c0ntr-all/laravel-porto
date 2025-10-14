<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\Actions;

use App\Containers\LifelogSection\Post\Data\DTO\PostListDto;
use App\Containers\LifelogSection\Post\Tasks\ListPostsTask;
use App\Containers\LifelogSection\Post\UI\API\Requests\ListPostsRequest;
use App\Containers\LifelogSection\Post\UI\API\Transformers\PostTransformer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class ListPostsAction
{
    use AsAction;

    public function __construct(
        private readonly ListPostsTask $listPostsTask
    )
    {
    }

    public function handle(PostListDto $dto): Collection
    {
        return $this->listPostsTask->run($dto);
    }

    public function asController(ListPostsRequest $request): JsonResponse
    {
        $dto = PostListDto::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $posts = $this->handle($dto);

        return fractal($posts, new PostTransformer($dto->user_id))
            ->parseIncludes(['user', 'tags', 'attachments'])
            ->withResourceName('posts')
            ->addMeta(['count' => $posts->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
