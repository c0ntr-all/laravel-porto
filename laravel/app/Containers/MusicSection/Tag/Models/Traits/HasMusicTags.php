<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Models\Traits;

use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Containers\MusicSection\Tag\Models\MusicTagable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasMusicTags
{
    /**
     * Удаляет связь между моделью и тегом, если модель была удалена
     */
    protected static function bootHasMusicTags(): void
    {
        static::deleting(fn($item) => $item->tags()->detach());
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(MusicTag::class, 'tagable', 'music_tagables', 'tagable_id', 'tag_id')
                    ->withTimestamps()
                    ->using(MusicTagable::class)
                    ->withPivot('tag_group_id')
                    ->as('tagables');
    }
}
