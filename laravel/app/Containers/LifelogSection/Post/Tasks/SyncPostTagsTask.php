<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tasks;

use App\Containers\AppSection\Tag\Data\DTO\SyncTagsDto;
use App\Containers\AppSection\Tag\Data\DTO\TagsCreateDto;
use App\Containers\AppSection\Tag\Tasks\CreateTagsByNamesTask;
use App\Containers\AppSection\Tag\Tasks\SyncTagsTask;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SyncPostTagsTask extends ParentTask
{
    public function __construct(
        private readonly CreateTagsByNamesTask $createTagsByNamesTask,
        private readonly SyncTagsTask $syncTagsTask,
    )
    {
    }

    /**
     * @param Post $post
     * @param int $userId
     * @param array $newTags
     * @param array $existingTagIds
     * @return void
     */
    public function run(Post $post, int $userId, array $newTags = [], array $existingTagIds = []): void
    {
        if (!empty($newTags)) {
            $newTagsDto = TagsCreateDto::from([
                'user_id' => $userId,
                'names' => $newTags,
            ]);

            $newTagsCollection = $this->createTagsByNamesTask->run($newTagsDto);
            $existingTagIds = array_merge($existingTagIds, $newTagsCollection->pluck('id')->toArray());
        }

        if (!empty($existingTagIds)) {
            $syncData = collect($existingTagIds)
                ->mapWithKeys(fn ($tagId) => [$tagId => ['user_id' => $userId]])
                ->toArray();

            $syncTagsDto = SyncTagsDto::from(['tags' => $syncData]);
            $this->syncTagsTask->run($post, $syncTagsDto);
        }
    }
}
