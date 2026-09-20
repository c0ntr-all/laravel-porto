<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Support;

use App\Containers\MovieSection\Movie\Models\Movie;

class MovieStateSnapshot
{
    /**
     * @return array{
     *     id: int,
     *     kp_id: int,
     *     title: string,
     *     description: string|null,
     *     short_description: string|null,
     *     year: int,
     *     type: string,
     *     cover: string|null,
     *     kp_rating: float|null,
     *     kp_img: string|null,
     *     genres: list<string>,
     *     countries: list<string>
     * }
     */
    public static function fromMovie(Movie $movie): array
    {
        $movie->loadMissing(['genres', 'countries']);

        return [
            'id' => (int) $movie->id,
            'kp_id' => (int) $movie->kp_id,
            'title' => $movie->title,
            'description' => $movie->description,
            'short_description' => $movie->short_description,
            'year' => (int) $movie->year,
            'type' => $movie->type->value,
            'cover' => $movie->cover,
            'kp_rating' => $movie->kp_rating !== null ? round((float) $movie->kp_rating, 1) : null,
            'kp_img' => $movie->kp_img,
            'genres' => $movie->genres->pluck('name')->sort()->values()->all(),
            'countries' => $movie->countries->pluck('name')->sort()->values()->all(),
        ];
    }

    /**
     * @param array<string, mixed>|null $before
     * @param array<string, mixed> $after
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public static function diff(?array $before, array $after): array
    {
        if ($before === null) {
            return [];
        }

        $changes = [];
        foreach (['title', 'description', 'short_description', 'year', 'type', 'cover', 'kp_rating', 'kp_img', 'genres', 'countries'] as $key) {
            $old = $before[$key] ?? null;
            $new = $after[$key] ?? null;
            if (self::same($old, $new)) {
                continue;
            }

            $changes[$key] = [
                'old' => $old,
                'new' => $new,
            ];
        }

        return $changes;
    }

    private static function same(mixed $left, mixed $right): bool
    {
        if (is_array($left) || is_array($right)) {
            return $left === $right;
        }

        if (is_float($left) || is_float($right)) {
            return round((float) $left, 1) === round((float) $right, 1);
        }

        return $left === $right;
    }
}
