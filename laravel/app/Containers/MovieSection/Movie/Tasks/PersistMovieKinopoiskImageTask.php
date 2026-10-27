<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Tasks;

use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Movie\Support\KinopoiskMovieImagePath;
use App\Ship\Jobs\PersistRemoteImageJob;
use App\Ship\Parents\Tasks\Task as ParentTask;

class PersistMovieKinopoiskImageTask extends ParentTask
{
    /**
     * Downloads kp_img into local storage and writes the relative path to img.
     * Runs synchronously so import/API callers get a persisted image.
     */
    public function run(Movie $movie, bool $force = false): Movie
    {
        PersistRemoteImageJob::dispatchSync(
            modelClass: Movie::class,
            modelId: (int) $movie->id,
            sourceAttribute: KinopoiskMovieImagePath::SOURCE_ATTRIBUTE,
            targetAttribute: KinopoiskMovieImagePath::TARGET_ATTRIBUTE,
            directory: KinopoiskMovieImagePath::directory((int) $movie->id),
            disk: KinopoiskMovieImagePath::DISK,
            filename: KinopoiskMovieImagePath::FILENAME,
            force: $force,
            logContext: 'movie.kinopoisk_image',
        );

        return $movie->refresh();
    }
}
