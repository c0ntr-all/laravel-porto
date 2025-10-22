<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\Actions;

use App\Containers\AppSection\Attachment\Data\DTO\AttachmentsDeleteDto;
use App\Containers\AppSection\Attachment\Tasks\DeleteAttachmentsTask;
use App\Containers\LifelogSection\Post\Data\DTO\PostTagsSyncDto;
use App\Containers\LifelogSection\Post\Data\DTO\PostUpdateDto;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\LifelogSection\Post\Tasks\SyncPostTagsTask;
use App\Containers\LifelogSection\Post\Tasks\UpdatePostTask;
use App\Containers\LifelogSection\Post\UI\API\Requests\UpdateRequest;
use App\Containers\LifelogSection\Post\UI\API\Transformers\PostTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdatePostAction
{
    use AsAction;

    public function __construct(
        private readonly UpdatePostTask        $updatePostTask,
        private readonly SyncPostTagsTask      $syncPostTagsTask,
        private readonly DeleteAttachmentsTask $deleteAttachmentsTask,
    )
    {
    }

    public function handle(
        Post                 $post,
        PostUpdateDto        $postDto,
        PostTagsSyncDto      $tagsDto,
        AttachmentsDeleteDto $attachmentsDeleteDto
    ): Post
    {
        $updatedPost = $this->updatePostTask->run($post, $postDto);

        $this->syncPostTagsTask->run(
            post: $updatedPost,
            userId: $tagsDto->user_id,
            newTags: $tagsDto->new_tags,
            existingTagIds: $tagsDto->tags
        );
        $this->deleteAttachmentsTask->run(
            model: $updatedPost,
            dto: $attachmentsDeleteDto
        );

        return $updatedPost;
    }

    public function asController(Post $post, UpdateRequest $request): JsonResponse
    {
        $postDto = PostUpdateDto::from([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        $tagsDto = PostTagsSyncDto::from([
            ...$request->validated(),
            'user_id' => $postDto->user_id,
        ]);

        $attachmentsDeleteDto = AttachmentsDeleteDto::from([
            ...$request->validated(),
            'user_id' => $postDto->user_id,
        ]);

        $post = $this->handle($post, $postDto, $tagsDto, $attachmentsDeleteDto);

        return fractal($post, new PostTransformer($postDto->user_id))
            ->parseIncludes(['user', 'tags', 'attachments'])
            ->withResourceName('posts')
            ->addMeta(['message' => 'Post successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
