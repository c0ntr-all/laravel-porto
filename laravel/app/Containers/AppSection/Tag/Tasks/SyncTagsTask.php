<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Tasks;

use App\Containers\AppSection\Tag\Data\DTO\TagsSyncDto;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Model;

class SyncTagsTask extends ParentTask
{
    /**
     * @param Model $model
     * @param TagsSyncDto $dto
     * @return array
     */
    public function run(Model $model, TagsSyncDto $dto): array
    {
        return $model->tags()->sync($dto->tags);
    }
}
