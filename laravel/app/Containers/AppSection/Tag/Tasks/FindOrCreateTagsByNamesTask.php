<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Tasks;

use App\Containers\AppSection\Tag\Data\DTO\TagCreateDto;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

class FindOrCreateTagsByNamesTask extends Task
{
    public function __construct(
        private readonly ListTagsByWhereInTask $listTagsByWhereInTask,
        private readonly CreateTagTask $createTagTask,
    ) {
    }

    public function run(array $names, int $userId): Collection
    {
        $names = array_values(array_unique(array_filter($names)));

        if (empty($names)) {
            return new Collection();
        }

        $tags = $this->listTagsByWhereInTask->run('name', $names, $userId);
        $existingNames = $tags->pluck('name')->all();
        $missingNames = array_values(array_diff($names, $existingNames));

        foreach ($missingNames as $name) {
            $tags->push($this->createTagTask->run(TagCreateDto::from([
                'user_id' => $userId,
                'name' => $name,
            ])));
        }

        return $tags;
    }
}
