<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Data\Repositories;

use App\Containers\LifelogSection\Preset\Data\DTO\PresetCreateDto;
use App\Containers\LifelogSection\Preset\Models\Preset;
use Illuminate\Database\Eloquent\Collection;

class PresetRepository
{
    public function get(array $data): Collection
    {
        return Preset::whereUserId($data['user_id'])
            ->with(['tags'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function createPreset(PresetCreateDto $dto): Preset
    {
        return Preset::create($dto->toArray());
    }

    public function deletePreset(Preset $preset): ?bool
    {
        return $preset->delete();
    }
}
