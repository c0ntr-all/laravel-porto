<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\Actions;

use App\Containers\AppSection\Attachment\Data\DTO\AttachmentsDeleteDto;
use App\Containers\AppSection\Attachment\Tasks\DeleteAttachmentsTask;
use App\Containers\AppSection\Tag\Data\DTO\TagsCreateDto;
use App\Containers\AppSection\Tag\Tasks\CreateTagsByNamesTask;
use App\Containers\LifelogSection\Post\Data\DTO\PostUpdateContextDto;
use App\Containers\LifelogSection\Post\Data\DTO\PostUpdateDto;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\LifelogSection\Post\Tasks\AttachPostContentTask;
use App\Containers\LifelogSection\Post\Tasks\ListTagsByNamesTask;
use App\Containers\AppSection\Attachment\Tasks\CreateAttachmentsTask;
use App\Containers\LifelogSection\Post\Tasks\SyncPostTagsTask;
use App\Containers\LifelogSection\Post\Tasks\UpdatePostTask;
use App\Containers\LifelogSection\Post\UI\API\Requests\UpdateRequest;
use App\Containers\LifelogSection\Post\UI\API\Transformers\PostTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;

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
        private readonly CreateAttachmentsTask $createAttachmentsTask,
        private readonly AttachPostContentTask $attachPostContentTask,
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
            $newTags = $this->optionalArray($postUpdateContextDto->new_tags);
            $existingTagIds = $this->optionalArray($postUpdateContextDto->tags);

            // Проверяем существуют ли теги из тех, что присланы как новые
            if ($newTags !== []) {
                $existingNewTags = $this->listTagsByNamesTask->run(
                    $newTags,
                    $postUpdateContextDto->user_id
                );
                $existingNewTags?->each(function ($existingTag) use (&$tagsIdsForSync, &$newTags) {
                    $tagsIdsForSync[] = $existingTag->id;
                    $index = array_search($existingTag->name, $newTags, true);
                    if ($index !== false) {
                        unset($newTags[$index]);
                    }
                });

                // Остались еще теги после проверки?
                if ($newTags !== []) {
                    $newTags = array_values($newTags);
                    $createdTags = $this->createTagsByNamesTask->run(TagsCreateDto::from([
                        'user_id' => $postUpdateContextDto->user_id,
                        'new_tags' => $newTags,
                    ]));

                    if ($createdTags) {
                        $tagsIdsForSync = array_merge($tagsIdsForSync, $createdTags->pluck('id')->toArray());
                    }
                }
            }

            if ($existingTagIds !== []) {
                $tagsIdsForSync = array_merge($tagsIdsForSync, $existingTagIds);
            }

            if ($tagsIdsForSync !== []) {
                $this->syncPostTagsTask->run($post, $postUpdateDto->user_id, $tagsIdsForSync);
            }

            $attachments = $this->optionalArray($postUpdateContextDto->attachments);
            if ($attachments !== []) {
                $this->createAttachmentsTask->run(
                    $post,
                    $postUpdateContextDto->user_id,
                    ContainerAliasEnum::LL_POST->value,
                    $attachments
                );
            }

            $deletedAttachmentIds = $this->optionalArray($postUpdateContextDto->deleted_attachments_ids);
            if ($deletedAttachmentIds !== []) {
                $this->deleteAttachmentsTask->run(
                    model: $updatedPost,
                    dto: AttachmentsDeleteDto::from([
                        'user_id' => $postUpdateContextDto->user_id,
                        'deleted_attachments_ids' => $deletedAttachmentIds,
                    ])
                );
            }

            $contentAttachDto = $postUpdateContextDto->toContentAttachDto($updatedPost->content_type);
            if ($contentAttachDto !== null) {
                $this->attachPostContentTask->run($updatedPost, $contentAttachDto);
            }

            return $updatedPost->load(['movies.genres', 'movies.countries']);
        });

        $this->recordUseCase($updatedPost);

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
            ->parseIncludes(['user', 'tags', 'attachments', 'movies.genres', 'movies.countries'])
            ->withResourceName(ContainerAliasEnum::LL_POST->value)
            ->addMeta(['message' => 'Post successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }

    /**
     * @return list<mixed>
     */
    private function optionalArray(mixed $value): array
    {
        if ($value instanceof Optional || $value === null) {
            return [];
        }

        return is_array($value) ? array_values($value) : [];
    }
}
