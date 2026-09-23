<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Models\Traits;

use App\Containers\LifelogSection\Post\Data\ValueObjects\SeriesWatchProgress;
use App\Containers\LifelogSection\Post\Models\PostSubjectable;
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
        )
            ->using(PostSubjectable::class)
            ->withPivot('payload')
            ->withTimestamps();
    }

    public function syncMovies(array $movieIds): void
    {
        $this->movies()->sync($movieIds);
    }

    public function attachMovie(Movie $movie, ?SeriesWatchProgress $watch = null): void
    {
        $this->movies()->sync([
            $movie->id => [
                'payload' => $watch !== null
                    ? json_encode($watch->toArray(), JSON_THROW_ON_ERROR)
                    : null,
            ],
        ]);
    }

    public function watchProgress(): ?SeriesWatchProgress
    {
        $movie = $this->relationLoaded('movies')
            ? $this->movies->first()
            : $this->movies()->first();

        if (!$movie) {
            return null;
        }

        $payload = $movie->pivot?->payload;
        if (!is_array($payload) || $payload === []) {
            return null;
        }

        return SeriesWatchProgress::fromArray($payload);
    }
}
