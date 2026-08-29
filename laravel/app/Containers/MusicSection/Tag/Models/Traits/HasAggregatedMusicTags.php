<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Models\Traits;

use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Containers\MusicSection\Tag\Models\MusicUserTag;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasAggregatedMusicTags
{
    abstract protected function aggregatedTagsTable(): string;

    abstract protected function aggregatedTagsForeignKey(): string;

    abstract protected function userAggregatedTagsTable(): string;

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            MusicTag::class,
            $this->aggregatedTagsTable(),
            $this->aggregatedTagsForeignKey(),
            'tag_id',
        )
            ->withPivot(['tracks_count', 'percentage'])
            ->withTimestamps();
    }

    public function userTags(): BelongsToMany
    {
        return $this->belongsToMany(
            MusicUserTag::class,
            $this->userAggregatedTagsTable(),
            $this->aggregatedTagsForeignKey(),
            'tag_id',
        )
            ->withPivot(['user_id', 'tracks_count', 'percentage'])
            ->withTimestamps();
    }

    public function userTagsFor(int $userId): BelongsToMany
    {
        return $this->userTags()->wherePivot('user_id', $userId);
    }
}
