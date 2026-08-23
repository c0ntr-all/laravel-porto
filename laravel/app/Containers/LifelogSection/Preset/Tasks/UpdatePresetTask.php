<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Tasks;

use App\Containers\AppSection\Tag\Tasks\FindOrCreateTagsByNamesTask;
use App\Containers\LifelogSection\Preset\Data\DTO\PresetUpdateDto;
use App\Containers\LifelogSection\Preset\Data\Repositories\PresetRepository;
use App\Containers\LifelogSection\Preset\Data\ValueObjects\PresetRules;
use App\Containers\LifelogSection\Preset\Models\Preset;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

class UpdatePresetTask extends ParentTask
{
    public function __construct(
        private readonly PresetRepository $presetRepository,
        private readonly FindOrCreateTagsByNamesTask $findOrCreateTagsByNamesTask,
        private readonly SyncPresetTagsTask $syncPresetTagsTask,
    )
    {
    }

    public function run(Preset $preset, PresetUpdateDto $dto): Preset
    {
        return DB::transaction(function () use ($preset, $dto) {
            $preset = $this->presetRepository->updatePreset($preset, $dto);

            if ($dto->rules instanceof PresetRules) {
                $tags = $this->findOrCreateTagsByNamesTask->run($dto->rules->tags, $dto->user_id);
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
