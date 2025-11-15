<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tasks;

use App\Containers\AppSection\Tag\Tasks\ListTagsByWhereInTask;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListTagsByNamesTask extends ParentTask
{
    public function __construct(
        private readonly ListTagsByWhereInTask $listTagsByWhereInTask
    )
    {
    }

    /**
     * Проверка существования тегов по полю name из массива name
     *
     * @param array $tagNames
     * @return Collection|null
     */
    public function run(array $tagNames): ?Collection
    {
        // TODO: cross-section dependency
        return $this->listTagsByWhereInTask->run('name', $tagNames);
    }
}
