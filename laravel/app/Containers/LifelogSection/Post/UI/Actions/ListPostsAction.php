<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\Actions;

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

    public function handle(): Collection
    {
        return $this->listPostsTask->run();
    }

    public function asController(ListPostsRequest $request): JsonResponse
    {
        $posts = $this->handle();

        return fractal($posts, new PostTransformer())
            ->parseIncludes(['user'])
            ->withResourceName('posts')
            ->addMeta(['count' => $posts->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
