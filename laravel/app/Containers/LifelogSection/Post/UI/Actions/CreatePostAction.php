<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUseCaseTask;
use App\Containers\AppSection\Tag\Data\DTO\TagsCreateDto;
use App\Containers\AppSection\Tag\Tasks\CreateTagsByNamesTask;
use App\Containers\LifelogSection\Post\Data\DTO\PostCreateDto;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\LifelogSection\Post\Tasks\ListTagsByNamesTask;
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
        private readonly ListTagsByNamesTask       $listTagsByNamesTask,
        private readonly CreateTagsByNamesTask     $createTagsByNamesTask,
        private readonly SyncPostTagsTask          $syncPostTagsTask,
        private readonly CreateActivityUseCaseTask $createActivityUseCaseTask,
    )
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle(PostCreateDto $postCreateDto): Post
    {
        $post = DB::transaction(function() use ($postCreateDto) {
            $post = $this->createPostTask->run($postCreateDto);

            $tagsCreateDto = TagsCreateDto::from($postCreateDto->toArray());
            $tagsIdsForSync = [];

            // Проверяем существуют ли теги из тех, что присланы как новые
            if (!empty($tagsCreateDto->new_tags)) {
                $existingNewTags = $this->listTagsByNamesTask->run($tagsCreateDto->new_tags);
                $existingNewTags?->each(function ($existingTag) use (&$tagsIdsForSync, &$tagsCreateDto) {
                    $tagsIdsForSync[] = $existingTag->id;
                    unset($tagsCreateDto->new_tags[array_search($existingTag->name, $tagsCreateDto->new_tags)]);
                });

                // Остались еще теги посли проверки?
                if (!empty($tagsCreateDto->new_tags)) {
                    $newTags = $this->createTagsByNamesTask->run($tagsCreateDto);

                    if ($newTags) {
                        $tagsIdsForSync = array_merge($tagsIdsForSync, $newTags->pluck('id')->toArray());
                    }
                }
            }
            if (!empty($postCreateDto->tags)) {
                $tagsIdsForSync = array_merge($tagsIdsForSync, $postCreateDto->tags);
            }

            if (!empty($tagsIdsForSync)) {
                $this->syncPostTagsTask->run($post, $postCreateDto->user_id, $tagsIdsForSync);
            }

            return $post;
        });

        // Сделать Event запускающий таску
        DB::afterCommit(function () use ($post) {
            $this->createActivityUseCaseTask->run($post, $this->eventTypesEnum->value);
        });

        return $post;
    }

    /**
     * @throws \Exception
     */
    public function asController(CreateRequest $request): JsonResponse
    {
        $postCreateDto = PostCreateDto::from([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        $post = $this->handle($postCreateDto);

        return fractal($post, new PostTransformer($postCreateDto->user_id))
            ->parseIncludes(['user', 'tags'])
            ->withResourceName('ll_posts')
            ->addMeta([
                'message' => 'New post successfully created!',
                'correlation_uuid' => Correlation::getUuid()
            ])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
