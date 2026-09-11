<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\Models\Traits;

use App\Containers\MovieSection\Genre\Models\Genre;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasGenres
{
    protected static function bootHasGenres(): void
    {
        static::deleting(fn ($item) => $item->genres()->detach());
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(
            Genre::class,
            'movie_genre',
            'movie_id',
            'genre_id',
        )->withTimestamps();
    }
}
