<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUseCaseTask;
use App\Containers\LifelogSection\Post\Data\DTO\PostCreateDto;
use App\Containers\LifelogSection\Post\Data\DTO\PostTagsSyncDto;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\LifelogSection\Post\Tasks\CreatePostTask;
use App\Containers\LifelogSection\Post\Tasks\SyncPostTagsTask;
use App\Containers\LifelogSection\Post\UI\API\Requests\CreateRequest;
use App\Containers\LifelogSection\Post\UI\API\Transformers\PostTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Helpers\Correlation;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CreatePostAction extends BaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::LL_POST;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;

    public function __construct(
        private readonly CreatePostTask            $createPostTask,
        private readonly SyncPostTagsTask          $syncPostTagsTask,
        private readonly CreateActivityUseCaseTask $createActivityUseCaseTask
    )
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle(PostCreateDto $postDto, PostTagsSyncDto $tagsDto): Post
    {
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
        DB::afterCommit(function () use ($post) {
            $this->createActivityUseCaseTask->run($post, $this->eventTypesEnum->value);
            Correlation::clear();
        });

        return $post;
    }

    /**
     * @throws \Exception
     */
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
