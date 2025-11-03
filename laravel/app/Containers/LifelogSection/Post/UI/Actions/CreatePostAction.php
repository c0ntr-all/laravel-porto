<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUserLogTask;
use App\Containers\AppSection\ActivityLog\Tasks\ListSystemLogsByUuid;
use App\Containers\AppSection\Tag\Tasks\CreateTagsByNamesTask;
use App\Containers\AppSection\Tag\Tasks\SyncTagsTask;
use App\Containers\LifelogSection\Post\Data\DTO\PostCreateDto;
use App\Containers\LifelogSection\Post\Data\DTO\PostTagsSyncDto;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\LifelogSection\Post\Tasks\CreatePostTask;
use App\Containers\LifelogSection\Post\Tasks\SyncPostTagsTask;
use App\Containers\LifelogSection\Post\UI\API\Requests\CreateRequest;
use App\Containers\LifelogSection\Post\UI\API\Transformers\PostTransformer;
use App\Ship\Helpers\Correlation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePostAction
{
    use AsAction;

    public function __construct(
        private readonly CreatePostTask $createPostTask,
        private readonly CreateTagsByNamesTask $createTagsByNamesTask,
        private readonly SyncTagsTask $syncTagsTask,
        private readonly SyncPostTagsTask $syncPostTagsTask,
        private readonly CreateActivityUserLogTask $createActivityUserLogTask,
        private readonly ListSystemLogsByUuid $listSystemLogsByUuid
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function handle(PostCreateDto $postDto, PostTagsSyncDto $tagsDto): Post
    {
        $correlationUuid = Correlation::init();

        DB::beginTransaction();

        try {
            $post = $this->createPostTask->run($postDto);

            $this->syncPostTagsTask->run(
                post: $post,
                userId: $tagsDto->user_id,
                newTags: $tagsDto->new_tags ?? [],
                existingTagIds: $tagsDto->tags ?? [],
            );

            // TODO: Сюда перенести логику привязывания вложений т.к. это будет слой аггрегатор для микросервисов

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Correlation::clear();
            throw $e;
        }

        // После успешного коммита формируем user_log
        DB::afterCommit(function () use ($correlationUuid) {
            $systemLogs = $this->listSystemLogsByUuid->run($correlationUuid);
            $this->createActivityUserLogTask->run($systemLogs);
            Correlation::clear();
        });

        return $post;
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $postDto = PostCreateDto::from([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        $tagsDto = PostTagsSyncDto::from([
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
