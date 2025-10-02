<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Models\Traits;

use App\Containers\AppSection\Tag\Models\Tag;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function scopeForUser($query, int $userId)
    {
        return $query->wherePivot('user_id', $userId);
    }

    /**
     * Получить список тегов
     *
     * @return MorphToMany
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(
            Tag::class,
            'taggable',
            'taggables',
            'taggable_id',
            'tag_id'
        )->withPivot('user_id')
         ->withTimestamps();
    }

    /**
     * Присвоить тег пользователю
     *
     * @param Tag $tag
     * @param int $userId
     * @return void
     */
    public function attachTag(Tag $tag, int $userId): void
    {
        $this->tags()->attach($tag->id, ['user_id' => $userId]);
    }

    /**
     * Удалить тег пользователя
     *
     * @param Tag $tag
     * @param int $userId
     * @return void
     */
    public function detachTag(Tag $tag, int $userId): void
    {
        $this->tags()->wherePivot('user_id', $userId)->detach($tag->id);
    }

    /**
     * Получить только теги для конкретного пользователя
     *
     * @param int $userId
     * @return BelongsToMany
     */
    public function tagsForUser(int $userId): BelongsToMany
    {
        return $this->tags()->wherePivot('user_id', $userId);
    }
}
