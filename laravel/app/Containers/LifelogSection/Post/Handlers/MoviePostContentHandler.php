<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Handlers;

use App\Containers\LifelogSection\Post\Contracts\PostContentHandlerInterface;
use App\Containers\LifelogSection\Post\Data\DTO\PostContentAttachDto;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Containers\MovieSection\Movie\Tasks\FindMovieByIdTask;
use App\Containers\MovieSection\Movie\Tasks\FindOrCreateMovieByTitleTask;
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
        if ($dto->movie_id !== null) {
            $movie = $this->findMovieByIdTask->run($dto->movie_id);
            $post->attachMovie($movie);

            return;
        }

        if ($dto->movie_title !== null && trim($dto->movie_title) !== '') {
            $type = $dto->content_type === PostContentTypeEnum::TV_SERIES
                ? MovieTypeEnum::TV_SERIES
                : MovieTypeEnum::MOVIE;

            $movie = $this->findOrCreateMovieByTitleTask->run($dto->movie_title, $type);
            $post->attachMovie($movie);

            return;
        }

        throw new InvalidArgumentException('Movie post requires movie_id or movie_title.');
    }

    public function eagerLoadRelations(): array
    {
        return ['movies.genres', 'movies.countries'];
    }
}
