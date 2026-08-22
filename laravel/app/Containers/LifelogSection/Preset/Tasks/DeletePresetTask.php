<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Tasks;

use App\Containers\LifelogSection\Preset\Data\Repositories\PresetRepository;
use App\Containers\LifelogSection\Preset\Models\Preset;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeletePresetTask extends ParentTask
{
    public function __construct(
        private readonly PresetRepository $presetRepository
    )
    {
    }

    public function run(Preset $preset): ?bool
    {
        return $this->presetRepository->deletePreset($preset);
    }
}
