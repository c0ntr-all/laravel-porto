<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Data\Repositories;

use App\Containers\MovieSection\Movie\Data\DTO\MovieCreateData;
use App\Containers\MovieSection\Movie\Data\DTO\MovieUpdateData;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\CursorPaginator;
use Spatie\LaravelData\Optional;
use Spatie\QueryBuilder\AllowedFilter;

class MovieRepository
{
    public function getWithCursor(): CursorPaginator
    {
        return QueryBuilder::for(Movie::class, request())
            ->allowedFilters($this->allowedFilters())
            ->allowedSorts(['title', 'year', 'kp_rating', 'created_at'])
            ->allowedIncludes(['genres', 'countries'])
            ->with(['genres', 'countries'])
            ->defaultSort('-created_at')
            ->orderByDesc('id')
            ->cursorPaginate(24);
    }

    public function create(MovieCreateData $dto): Movie
    {
        return Movie::create([
            'kp_id' => $dto->kp_id,
            'title' => $dto->title,
            'year' => $dto->year,
            'type' => $dto->type,
            'cover' => $dto->cover,
            'kp_rating' => $dto->kp_rating,
            'kp_img' => $dto->kp_img,
        ]);
    }

    public function update(Movie $movie, MovieUpdateData $dto): Movie
    {
        $attributes = [];

        foreach ($dto->toArray() as $key => $value) {
            if ($value instanceof Optional) {
                continue;
            }

            $attributes[$key] = $value;
        }

        if ($attributes !== []) {
            $movie->update($attributes);
        }

        return $movie->refresh();
    }

    public function delete(Movie $movie): bool
    {
        return (bool) $movie->delete();
    }

    /**
     * @param list<int> $genreIds
     */
    public function syncGenres(Movie $movie, array $genreIds): array
    {
        return $movie->genres()->sync($genreIds);
    }

    /**
     * @param list<int> $countryIds
     */
    public function syncCountries(Movie $movie, array $countryIds): array
    {
        return $movie->countries()->sync($countryIds);
    }

    /**
     * @return list<AllowedFilter>
     */
    private function allowedFilters(): array
    {
        return [
            AllowedFilter::partial('title'),
            AllowedFilter::exact('year'),
            AllowedFilter::exact('type'),
            AllowedFilter::exact('kp_id'),
            AllowedFilter::callback('genre_id', function (Builder $query, mixed $value): void {
                $ids = $this->intIds($value);
                if ($ids === []) {
                    return;
                }

                $query->whereHas('genres', function (Builder $genres) use ($ids): void {
                    $genres->whereIn('movie_genres.id', $ids);
                });
            }),
            AllowedFilter::callback('country_id', function (Builder $query, mixed $value): void {
                $ids = $this->intIds($value);
                if ($ids === []) {
                    return;
                }

                $query->whereHas('countries', function (Builder $countries) use ($ids): void {
                    $countries->whereIn('countries.id', $ids);
                });
            }),
        ];
    }

    /**
     * @return list<int>
     */
    private function intIds(mixed $value): array
    {
        if (is_string($value) || is_numeric($value)) {
            $value = preg_split('/\s*,\s*/', (string) $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->flatten()
            ->map(fn (mixed $id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
