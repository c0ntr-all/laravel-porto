<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\CreateActivityUseCaseTask;
use App\Containers\AppSection\Attachment\Data\DTO\AttachmentsDeleteDto;
use App\Containers\AppSection\Attachment\Tasks\DeleteAttachmentsTask;
use App\Containers\AppSection\Tag\Data\DTO\TagsCreateDto;
use App\Containers\AppSection\Tag\Tasks\CreateTagsByNamesTask;
use App\Containers\LifelogSection\Post\Data\DTO\PostTagsUpdateDto;
use App\Containers\LifelogSection\Post\Data\DTO\PostUpdateContextDto;
use App\Containers\LifelogSection\Post\Data\DTO\PostUpdateDto;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\LifelogSection\Post\Tasks\ListTagsByNamesTask;
use App\Containers\LifelogSection\Post\Tasks\CreatePostAttachmentsTask;
use App\Containers\LifelogSection\Post\Tasks\SyncPostTagsTask;
use App\Containers\LifelogSection\Post\Tasks\UpdatePostTask;
use App\Containers\LifelogSection\Post\UI\API\Requests\UpdateRequest;
use App\Containers\LifelogSection\Post\UI\API\Transformers\PostTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Helpers\Correlation;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UpdatePostAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::LL_POST;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;

    public function __construct(
        private readonly UpdatePostTask            $updatePostTask,
        private readonly ListTagsByNamesTask       $listTagsByNamesTask,
        private readonly CreateTagsByNamesTask     $createTagsByNamesTask,
        private readonly SyncPostTagsTask          $syncPostTagsTask,
        private readonly DeleteAttachmentsTask     $deleteAttachmentsTask,
        private readonly CreateActivityUseCaseTask $createActivityUseCaseTask,
        private readonly CreatePostAttachmentsTask $syncPostAttachmentsTask
    )
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle(
        Post                 $post,
        PostUpdateContextDto $postUpdateContextDto
    ): Post
    {
        //TODO: SubAction
        $updatedPost = DB::transaction(function() use ($post, $postUpdateContextDto) {
            $postUpdateDto = PostUpdateDto::from($postUpdateContextDto->toArray());
            $updatedPost = $this->updatePostTask->run($post, $postUpdateDto);

            $tagsIdsForSync = [];
            // Проверяем существуют ли теги из тех, что присланы как новые
            if (!empty($postUpdateContextDto->new_tags)) {
                $updateTagsDto = PostTagsUpdateDto::from($postUpdateContextDto->toArray());
                $existingNewTags = $this->listTagsByNamesTask->run($updateTagsDto->new_tags);
                $existingNewTags?->each(function ($existingTag) use (&$tagsIdsForSync, &$updateTagsDto) {
                    $tagsIdsForSync[] = $existingTag->id;
                    unset($updateTagsDto->new_tags[array_search($existingTag->name, $updateTagsDto->new_tags)]);
                });

                // Остались еще теги посли проверки?
                if (!empty($postUpdateContextDto->new_tags)) {
                    $newTags = $this->createTagsByNamesTask->run(TagsCreateDto::from($postUpdateContextDto->toArray()));

                    if ($newTags) {
                        $tagsIdsForSync = array_merge($tagsIdsForSync, $newTags->pluck('id')->toArray());
                    }
                }
            }
            if (!empty($postUpdateContextDto->tags)) {
                $tagsIdsForSync = array_merge($tagsIdsForSync, $postUpdateContextDto->tags);
            }

            if (!empty($tagsIdsForSync)) {
                $this->syncPostTagsTask->run($post, $postUpdateDto->user_id, $tagsIdsForSync);
            }

            if (!empty($postUpdateContextDto->attachments)) {
                $this->syncPostAttachmentsTask->run(
                    $post,
                    $postUpdateContextDto->user_id,
                    $postUpdateContextDto->attachments
                );
            }

            if (!empty($postUpdateContextDto->deleted_attachments_ids)) {
                $attachmentsDeleteDto = AttachmentsDeleteDto::from($postUpdateContextDto->toArray());

                $this->deleteAttachmentsTask->run(
                    model: $updatedPost,
                    dto: $attachmentsDeleteDto
                );
            }

            return $updatedPost;
        });

        // Сделать Event запускающий таску
        DB::afterCommit(function () use ($updatedPost) {
            $this->createActivityUseCaseTask->run($updatedPost, $this->eventTypesEnum->value);
        });

        return $updatedPost;
    }

    /**
     * @throws \Exception
     */
    public function asController(Post $post, UpdateRequest $request): JsonResponse
    {
        $postUpdateContextDto = PostUpdateContextDto::from([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        $post = $this->handle($post, $postUpdateContextDto);

        return fractal($post, new PostTransformer($postUpdateContextDto->user_id))
            ->parseIncludes(['user', 'tags', 'attachments'])
            ->withResourceName(ContainerAliasEnum::LL_POST->value)
            ->addMeta([
                'message' => 'Post successfully updated!',
                'correlation_uuid' => Correlation::getUuid()
            ])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
