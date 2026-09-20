<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\Filters;

use App\Containers\LifelogSection\Post\Tasks\ApplyPresetToPostsQueryTask;
use App\Containers\LifelogSection\Preset\Models\Preset;
use App\Ship\Parents\QueryBuilder\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PresetFilter implements Filter
{
    public function __construct(
        private readonly ApplyPresetToPostsQueryTask $applyPresetToPostsQueryTask,
    ) {
    }

    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $presetId = $this->resolvePresetId($value);
        if ($presetId === null) {
            return;
        }

        $userId = (int) auth()->id();

        $preset = Preset::query()
            ->where('user_id', $userId)
            ->whereKey($presetId)
            ->first();

        if (!$preset) {
            throw (new ModelNotFoundException())->setModel(Preset::class, [$presetId]);
        }

        $this->applyPresetToPostsQueryTask->run($query, $preset);
    }

    private function resolvePresetId(mixed $value): ?int
    {
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        $presetId = (int) $value;

        return $presetId > 0 ? $presetId : null;
    }
}
