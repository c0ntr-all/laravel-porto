<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Handlers;

use App\Containers\LifelogSection\Post\Contracts\PostContentHandlerInterface;
use App\Containers\LifelogSection\Post\Data\DTO\PostContentAttachDto;
use App\Containers\LifelogSection\Post\Data\ValueObjects\SeriesWatchProgress;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Movie\Tasks\FindMovieByIdTask;
use App\Containers\MovieSection\Movie\Tasks\FindOrCreateMovieByTitleTask;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class MoviePostContentHandler implements PostContentHandlerInterface
{
    public function __construct(
        private readonly FindMovieByIdTask $findMovieByIdTask,
        private readonly FindOrCreateMovieByTitleTask $findOrCreateMovieByTitleTask,
    ) {
    }

    public function supports(PostContentTypeEnum $contentType): bool
    {
        return $contentType === PostContentTypeEnum::MOVIE
            || $contentType === PostContentTypeEnum::TV_SERIES;
    }

    public function attach(Post $post, PostContentAttachDto $dto): void
    {
        $movie = $this->resolveMovie($post, $dto);
        $watch = $this->resolveWatch($post, $movie, $dto);

        $post->attachMovie($movie, $watch);
    }

    public function eagerLoadRelations(): array
    {
        return ['movies.genres', 'movies.countries'];
    }

    private function resolveMovie(Post $post, PostContentAttachDto $dto): Movie
    {
        if ($dto->movie_id !== null) {
            return $this->findMovieByIdTask->run($dto->movie_id);
        }

        if ($dto->movie_title !== null && trim($dto->movie_title) !== '') {
            $type = $dto->content_type === PostContentTypeEnum::TV_SERIES
                ? MovieTypeEnum::TV_SERIES
                : MovieTypeEnum::MOVIE;

            return $this->findOrCreateMovieByTitleTask->run($dto->movie_title, $type);
        }

        $existing = $this->currentMovie($post);

        if ($existing) {
            return $existing;
        }

        throw new InvalidArgumentException('Movie post requires movie_id or movie_title.');
    }

    private function resolveWatch(Post $post, Movie $movie, PostContentAttachDto $dto): ?SeriesWatchProgress
    {
        if ($dto->hasWatchPayload()) {
            if ($movie->type !== MovieTypeEnum::TV_SERIES) {
                throw ValidationException::withMessages([
                    'watch' => 'Watch progress is allowed only for tv_series.',
                ]);
            }

            return $dto->watchProgress();
        }

        $current = $this->currentMovie($post);
        if (
            $current
            && (int) $current->id === (int) $movie->id
            && $movie->type === MovieTypeEnum::TV_SERIES
        ) {
            $payload = $current->pivot?->payload;
            if (is_array($payload) && $payload !== []) {
                return SeriesWatchProgress::fromArray($payload);
            }
        }

        return null;
    }

    private function currentMovie(Post $post): ?Movie
    {
        return $post->relationLoaded('movies')
            ? $post->movies->first()
            : $post->movies()->first();
    }
}
