<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Models\Traits;

use App\Containers\MovieSection\Movie\Models\Movie;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasSubjects
{
    protected static function bootHasSubjects(): void
    {
        static::deleting(function ($post): void {
            $post->movies()->detach();
        });
    }

    public function movies(): MorphToMany
    {
        return $this->morphedByMany(
            Movie::class,
            'subjectable',
            'lifelog_post_subjectables',
            'post_id',
            'subjectable_id'
        )->withTimestamps();
    }

    public function syncMovies(array $movieIds): void
    {
        $this->movies()->sync($movieIds);
    }

    public function attachMovie(Movie $movie): void
    {
        $this->movies()->sync([$movie->id]);
    }
}
