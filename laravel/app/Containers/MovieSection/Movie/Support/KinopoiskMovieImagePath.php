<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Support;

/**
 * Storage layout for Kinopoisk-sourced movie posters.
 *
 * kinopoisk/movies/{movie_id}/poster.{ext}
 */
final class KinopoiskMovieImagePath
{
    public const DISK = 'public';

    public const SOURCE_ATTRIBUTE = 'kp_img';

    public const TARGET_ATTRIBUTE = 'img';

    public const FILENAME = 'poster';

    public static function directory(int|string $movieId): string
    {
        return 'kinopoisk/movies/'.$movieId;
    }
}
