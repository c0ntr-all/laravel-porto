<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tasks;

use App\Containers\AppSection\Tag\Data\DTO\TagsSyncDto;
use App\Containers\AppSection\Tag\Data\DTO\TagsCreateDto;
use App\Containers\AppSection\Tag\Tasks\CreateTagsByNamesTask;
use App\Containers\AppSection\Tag\Tasks\SyncTagsTask;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SyncPostTagsTask extends ParentTask
{
    public function __construct(
        private readonly CreateTagsByNamesTask $createTagsByNamesTask,
        private readonly SyncTagsTask          $syncTagsTask,
    )
    {
    }

    /**
     * @param Post $post
     * @param int $userId
     * @param array|null $newTags
     * @param array|null $existingTagIds
     * @return void
     */
    public function run(Post $post, int $userId, ?array $newTags, ?array $existingTagIds): void
    {
        $tagsIdsToSync = [];

        if (!is_null($newTags)) {
            $newTagsDto = TagsCreateDto::from([
                'user_id' => $userId,
                'names' => $newTags,
            ]);

            $newTagsCollection = $this->createTagsByNamesTask->run($newTagsDto);
            $tagsIdsToSync = $newTagsCollection->pluck('id')->toArray();
        }

        if (!is_null($existingTagIds)) {
            $tagsIdsToSync = array_merge($tagsIdsToSync, $existingTagIds);
        }

        $tagsIdsToSyncWithUserIds = collect($tagsIdsToSync)
            ->mapWithKeys(fn($tagId) => [$tagId => ['user_id' => $userId]])
            ->toArray();

        $syncTagsDto = TagsSyncDto::from(['tags' => $tagsIdsToSyncWithUserIds]);
        // TODO: Сделать слежение события синхронизации
        $this->syncTagsTask->run($post, $syncTagsDto);
    }
}
