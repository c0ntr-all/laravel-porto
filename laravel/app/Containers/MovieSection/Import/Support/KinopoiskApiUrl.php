<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Support;

class KinopoiskApiUrl
{
    public static function movie(int $kpId): string
    {
        $base = rtrim((string) config('movie_import.base_url'), '/');
        $path = (string) config('movie_import.movie_path', '/v1.5/movie/%d');

        if (!str_contains($path, '%')) {
            $path = rtrim($path, '/').'/'.$kpId;
        } else {
            $path = sprintf($path, $kpId);
        }

        if (!str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        return $base.$path;
    }

    /**
     * @param array<string, scalar|null> $query
     */
    public static function season(array $query = []): string
    {
        $base = rtrim((string) config('movie_import.base_url'), '/');
        $path = (string) config('movie_import.season_path', '/v1.5/season');

        if (!str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        $filtered = array_filter(
            $query,
            static fn (mixed $value): bool => $value !== null && $value !== '',
        );

        if ($filtered === []) {
            return $base.$path;
        }

        return $base.$path.'?'.http_build_query($filtered);
    }
}
