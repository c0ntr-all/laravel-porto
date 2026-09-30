<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Tasks;

use App\Ship\Parents\Tasks\Task;

class ResolveTagIdsForSyncTask extends Task
{
    public function __construct(
        private readonly FindOrCreateTagsByNamesTask $findOrCreateTagsByNamesTask,
    ) {
    }

    /**
     * @param list<int|string> $tagIds
     * @param list<string> $newTagNames
     * @return list<int>
     */
    public function run(int $userId, array $tagIds, array $newTagNames = []): array
    {
        $ids = array_map('intval', $tagIds);

        if ($newTagNames !== []) {
            $created = $this->findOrCreateTagsByNamesTask->run($newTagNames, $userId);
            $ids = array_merge($ids, $created->pluck('id')->all());
        }

        return array_values(array_unique($ids));
    }
}
