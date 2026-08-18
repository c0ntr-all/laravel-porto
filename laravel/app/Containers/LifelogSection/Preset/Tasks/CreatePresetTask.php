<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Tasks;

use App\Containers\AppSection\Tag\Tasks\FindOrCreateTagsByNamesTask;
use App\Containers\LifelogSection\Preset\Data\DTO\PresetCreateDto;
use App\Containers\LifelogSection\Preset\Data\Repositories\PresetRepository;
use App\Containers\LifelogSection\Preset\Models\Preset;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

class CreatePresetTask extends ParentTask
{
    public function __construct(
        private readonly PresetRepository $presetRepository,
        private readonly FindOrCreateTagsByNamesTask $findOrCreateTagsByNamesTask,
        private readonly SyncPresetTagsTask $syncPresetTagsTask,
    )
    {
    }

    public function run(PresetCreateDto $dto): Preset
    {
        return DB::transaction(function () use ($dto) {
            $preset = $this->presetRepository->createPreset($dto);

            if (!empty($dto->tags)) {
                $tags = $this->findOrCreateTagsByNamesTask->run($dto->tags, $dto->user_id);
                $this->syncPresetTagsTask->run(
                    $preset,
                    $dto->user_id,
                    $tags->pluck('id')->all()
                );
            }

            return $preset->load('tags');
        });
    }
}
