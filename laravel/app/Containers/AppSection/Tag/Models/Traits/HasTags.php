<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Models\Traits;

use App\Containers\AppSection\Tag\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTags
{
    /**
     * Удаляет связь между моделью и тегом, если модель была удалена
     */
    protected static function bootHasTags(): void
    {
        static::deleting(fn($item) => $item->tags()->detach());
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(
            Tag::class,
            'taggable',
            'taggables',
            'taggable_id',
            'tag_id'
        )->withTimestamps();
    }
}
