<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Models\Traits;

use App\Containers\LifelogSection\Post\Data\ValueObjects\MovieSubjectPayload;
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

    public function attachMovie(Movie $movie, ?MovieSubjectPayload $payload = null): void
    {
        // Pass a PHP array — PostSubjectable casts payload to JSON once.
        // Manual json_encode here would double-encode via the pivot cast.
        $this->movies()->sync([
            $movie->id => [
                'payload' => $payload?->isEmpty() ? null : $payload?->toArray(),
            ],
        ]);
    }

    public function movieSubjectPayload(): ?MovieSubjectPayload
    {
        $payload = $this->rawMovieSubjectPayload();
        if ($payload === null) {
            return null;
        }

        $subject = MovieSubjectPayload::fromArray($payload);

        return $subject->isEmpty() ? null : $subject;
    }

    public function watchProgress(): ?SeriesWatchProgress
    {
        return $this->movieSubjectPayload()?->watch;
    }

    public function startedAt(): ?string
    {
        return $this->movieSubjectPayload()?->startedAt;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function rawMovieSubjectPayload(): ?array
    {
        $movie = $this->relationLoaded('movies')
            ? $this->movies->first()
            : $this->movies()->first();

        if (!$movie) {
            return null;
        }

        return $this->normalizePivotPayload($movie->pivot?->payload);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizePivotPayload(mixed $payload): ?array
    {
        if (is_array($payload)) {
            // Recover legacy double-encoded payloads: ["{\"season\":1}"] shape as assoc after one decode
            if (
                isset($payload['season'])
                || isset($payload['episode_from'])
                || isset($payload['started_at'])
            ) {
                return $payload;
            }

            return $payload === [] ? null : $payload;
        }

        if (is_string($payload) && $payload !== '') {
            $decoded = json_decode($payload, true);

            // Double-encoded JSON string from previous bug
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }

            if (is_array($decoded) && $decoded !== []) {
                return $decoded;
            }
        }

        return null;
    }
}
