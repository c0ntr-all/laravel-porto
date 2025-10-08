<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\Actions;

use App\Containers\LifelogSection\Post\Data\DTO\PostTagsSaveDto;
use App\Containers\LifelogSection\Post\Data\DTO\PostUpdateDto;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\LifelogSection\Post\Tasks\SyncPostTagsTask;
use App\Containers\LifelogSection\Post\Tasks\UpdatePostTask;
use App\Containers\LifelogSection\Post\UI\API\Requests\UpdateRequest;
use App\Containers\LifelogSection\Post\UI\API\Transformers\PostTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\LaravelData\Optional;

class UpdatePostAction
{
    use AsAction;

    public function __construct(
        private readonly UpdatePostTask $updatePostTask,
        private readonly SyncPostTagsTask $syncPostTagsTask
    )
    {
    }

    public function handle(Post $post, PostUpdateDto $postDto, PostTagsSaveDto $tagsDto): Post
    {
        $updatedPost = $this->updatePostTask->run($post, $postDto);

        $this->syncPostTagsTask->run(
            post: $updatedPost,
            userId: $tagsDto->user_id,
            newTags: $tagsDto->new_tags,
            existingTagIds: $tagsDto->tags
        );

        return $updatedPost;
    }

    public function asController(Post $post, UpdateRequest $request): JsonResponse
    {
        $postDto = PostUpdateDto::from([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        $tagsDto = PostTagsSaveDto::from([
            ...$request->validated(),
            'user_id' => $postDto->user_id,
        ]);

        $post = $this->handle($post, $postDto, $tagsDto);

        return fractal($post, new PostTransformer($postDto->user_id))
            ->parseIncludes(['user', 'tags'])
            ->withResourceName('posts')
            ->addMeta(['message' => 'Post successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
