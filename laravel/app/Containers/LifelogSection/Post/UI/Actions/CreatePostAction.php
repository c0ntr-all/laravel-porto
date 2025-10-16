<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\Actions;

use App\Containers\AppSection\Tag\Tasks\CreateTagsByNamesTask;
use App\Containers\AppSection\Tag\Tasks\SyncTagsTask;
use App\Containers\LifelogSection\Post\Data\DTO\PostCreateDto;
use App\Containers\LifelogSection\Post\Data\DTO\PostTagsSaveDto;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\LifelogSection\Post\Tasks\CreatePostTask;
use App\Containers\LifelogSection\Post\Tasks\SyncPostTagsTask;
use App\Containers\LifelogSection\Post\UI\API\Requests\CreateRequest;
use App\Containers\LifelogSection\Post\UI\API\Transformers\PostTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePostAction
{
    use AsAction;

    public function __construct(
        private readonly CreatePostTask $createPostTask,
        private readonly CreateTagsByNamesTask $createTagsByNamesTask,
        private readonly SyncTagsTask $syncTagsTask,
        private readonly SyncPostTagsTask $syncPostTagsTask
    )
    {
    }

    public function handle(PostCreateDto $postDto, PostTagsSaveDto $tagsDto): Post
    {
        $post = $this->createPostTask->run($postDto);

        $this->syncPostTagsTask->run(
            post: $post,
            userId: $tagsDto->user_id,
            newTags: $tagsDto->new_tags ?? [],
            existingTagIds: $tagsDto->tags ?? []
        );

        return $post;
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $postDto = PostCreateDto::from([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        $tagsDto = PostTagsSaveDto::from([
            ...$request->validated(),
            'user_id' => $postDto->user_id,
        ]);

        $post = $this->handle($postDto, $tagsDto);

        return fractal($post, new PostTransformer($postDto->user_id))
            ->parseIncludes(['user', 'tags'])
            ->withResourceName('ll_posts')
            ->addMeta(['message' => 'New post successfully created!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
