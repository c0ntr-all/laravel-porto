<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Tag\Data\DTO\SyncTagsDto;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Model;

class SyncTagsTask extends ParentTask
{
    /**
     * @param Model $model
     * @param SyncTagsDto $dto
     * @return array
     */
    public function run(Model $model, SyncTagsDto $dto): array
    {
        return $model->tags()->sync($dto->tags);
    }
}
