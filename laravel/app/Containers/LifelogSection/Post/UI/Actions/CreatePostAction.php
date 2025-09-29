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
        $requestData = $request->validated();
        $dto = PostCreateData::from($requestData);
        $dto->user_id = auth()->user()->id;

        $post = $this->handle($dto);

        if (!empty($requestData['tags'])) {
            $post->tags()->sync($requestData['tags']);
        }

        return fractal($post, new PostTransformer())
            ->parseIncludes(['user', 'tags'])
            ->withResourceName('posts')
            ->addMeta(['message' => 'New post successfully created!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
