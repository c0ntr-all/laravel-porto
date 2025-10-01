<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\Actions;

use App\Containers\AppSection\Tag\Data\DTO\TagsCreateDto;
use App\Containers\AppSection\Tag\Tasks\CreateTagsByNamesTask;
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
        private readonly CreatePostTask $createPostTask,
        private readonly CreateTagsByNamesTask $createTagsByNamesTask
    )
    {
    }

    public function handle(PostCreateData $dto): Post
    {
        $post = $this->createPostTask->run($dto);

        if (!empty($dto->new_tags)) {
            $newTagsDto = TagsCreateDto::from([
                'user_id' => $dto->user_id,
                'names' => $dto->new_tags
            ]);
            $newTags = $this->createTagsByNamesTask->run($newTagsDto);

            $dto->tags = array_merge($dto->tags, $newTags->pluck('id')->toArray());
        }

        if (!empty($dto->tags)) {
            $post->tags()->sync($dto->tags);
        }

        return $post;
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $dto = PostCreateData::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $post = $this->handle($dto);

        return fractal($post, new PostTransformer())
            ->parseIncludes(['user', 'tags'])
            ->withResourceName('posts')
            ->addMeta(['message' => 'New post successfully created!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
