<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Tasks;

use App\Containers\AppSection\Tag\Data\DTO\TagsSyncDto;
use App\Containers\AppSection\Tag\Tasks\SyncTagsTask;
use App\Containers\LifelogSection\Preset\Models\Preset;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SyncPresetTagsTask extends ParentTask
{
    public function __construct(
        private readonly SyncTagsTask $syncTagsTask,
    )
    {
    }

    public function run(Preset $preset, int $userId, array $tagIds): void
    {
        $tagsIdsToSyncWithUserIds = collect($tagIds)
            ->mapWithKeys(fn ($tagId) => [$tagId => ['user_id' => $userId]])
            ->toArray();

        $syncTagsDto = TagsSyncDto::from(['tags' => $tagsIdsToSyncWithUserIds]);

        $this->syncTagsTask->run($preset, $syncTagsDto);
    }
}
