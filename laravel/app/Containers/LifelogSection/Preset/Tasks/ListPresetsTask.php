<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Tasks;

use App\Containers\LifelogSection\Preset\Data\DTO\PresetListDto;
use App\Containers\LifelogSection\Preset\Data\Repositories\PresetRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListPresetsTask extends ParentTask
{
    public function __construct(
        private readonly PresetRepository $presetRepository
    )
    {
    }

    public function run(PresetListDto $dto): Collection
    {
        return $this->presetRepository->get([
            'user_id' => $dto->user_id,
        ]);
    }
}
