<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Tasks;

use App\Containers\AppSection\Tag\Data\DTO\TagsSyncDto;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Database\Eloquent\Model;

class SyncModelTagsTask extends Task
{
    public function __construct(
        private readonly ResolveTagIdsForSyncTask $resolveTagIdsForSyncTask,
        private readonly SyncTagsTask $syncTagsTask,
    ) {
    }

    /**
     * @param list<int|string> $tagIds
     * @param list<string> $newTagNames
     */
    public function run(Model $model, int $userId, array $tagIds, array $newTagNames = []): array
    {
        $ids = $this->resolveTagIdsForSyncTask->run($userId, $tagIds, $newTagNames);

        $payload = collect($ids)
            ->mapWithKeys(fn (int $tagId) => [$tagId => ['user_id' => $userId]])
            ->all();

        return $this->syncTagsTask->run($model, TagsSyncDto::from(['tags' => $payload]));
    }
}
