<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tasks;

use App\Containers\LifelogSection\Preset\Models\Preset;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Builder;

class ApplyPresetToPostsQueryTask extends ParentTask
{
    public function run(Builder $query, Preset $preset): void
    {
        $this->applyDateRange($query, $preset);
        $this->applyTags($query, $preset);
        $this->applyContentTypes($query, $preset);
        $this->applyText($query, $preset);
    }

    private function applyDateRange(Builder $query, Preset $preset): void
    {
        if ($preset->start_date !== null) {
            $query->whereDate('date', '>=', $preset->start_date->toDateString());
        }

        if ($preset->end_date !== null) {
            $query->whereDate('date', '<=', $preset->end_date->toDateString());
        }
    }

    private function applyTags(Builder $query, Preset $preset): void
    {
        $tagIds = $preset->tagsForUser($preset->user_id)
            ->pluck('tags.id')
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->values()
            ->all();

        if ($tagIds === []) {
            $tagNames = $preset->rules?->tags ?? [];
            if ($tagNames === []) {
                return;
            }

            $query->whereHas('tags', function (Builder $tags) use ($tagNames, $preset): void {
                $tags->whereIn('tags.name', $tagNames)
                    ->where('taggables.user_id', $preset->user_id);
            });

            return;
        }

        $query->whereHas('tags', function (Builder $tags) use ($tagIds, $preset): void {
            $tags->whereIn('tags.id', $tagIds)
                ->where('taggables.user_id', $preset->user_id);
        });
    }

    private function applyContentTypes(Builder $query, Preset $preset): void
    {
        $contentTypes = $preset->rules?->contentTypes ?? [];
        if ($contentTypes === []) {
            return;
        }

        $query->whereIn('content_type', $contentTypes);
    }

    private function applyText(Builder $query, Preset $preset): void
    {
        $text = trim((string) ($preset->rules?->text ?? ''));
        if ($text === '') {
            return;
        }

        $pattern = '%' . addcslashes($text, '%_\\') . '%';

        $query->where(function (Builder $builder) use ($pattern): void {
            $builder->where('title', 'like', $pattern)
                ->orWhere('content', 'like', $pattern);
        });
    }
}
