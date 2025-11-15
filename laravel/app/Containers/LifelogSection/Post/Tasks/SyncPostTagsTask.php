<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tasks;

use App\Containers\AppSection\Tag\Data\DTO\TagsSyncDto;
use App\Containers\AppSection\Tag\Tasks\SyncTagsTask;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SyncPostTagsTask extends ParentTask
{
    public function __construct(
        private readonly SyncTagsTask $syncTagsTask,
    )
    {
    }

    /**
     * @param Post $post
     * @param int $userId
     * @param array $tagsIdsForSync
     * @return void
     */
    public function run(Post $post, int $userId, array $tagsIdsForSync): void
    {
        $tagsIdsToSyncWithUserIds = collect($tagsIdsForSync)
            ->mapWithKeys(fn($tagId) => [$tagId => ['user_id' => $userId]])
            ->toArray();

        $syncTagsDto = TagsSyncDto::from(['tags' => $tagsIdsToSyncWithUserIds]);

        // TODO: cross-section dependency
        $this->syncTagsTask->run($post, $syncTagsDto);
    }
}
