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
            ->with(['startPost', 'endPost'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function createPreset(PresetCreateDto $dto): Preset
    {
        return Preset::create([
            'user_id' => $dto->user_id,
            'title' => $dto->title,
            'color' => $dto->color,
            'start_post_id' => $dto->start_post_id,
            'end_post_id' => $dto->end_post_id,
        ]);
    }
}
