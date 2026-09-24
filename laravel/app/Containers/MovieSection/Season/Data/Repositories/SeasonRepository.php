<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\Data\Repositories;

use App\Containers\MovieSection\Season\Data\DTO\SeasonCreateData;
use App\Containers\MovieSection\Season\Data\DTO\SeasonUpdateData;
use App\Containers\MovieSection\Season\Models\Season;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;

class SeasonRepository
{
    public function get(): Collection
    {
        return QueryBuilder::for(Season::class, request())
            ->allowedFilters([
                AllowedFilter::exact('movie_id'),
                AllowedFilter::exact('number'),
                AllowedFilter::exact('kp_id'),
                AllowedFilter::exact('kp_season_id'),
            ])
            ->allowedSorts(['number', 'air_date', 'created_at'])
            ->allowedIncludes([
                AllowedInclude::relationship('episodes'),
                AllowedInclude::relationship('movie'),
            ])
            ->with([
                'watches' => Season::constrainWatchesToCurrentUser(),
            ])
            ->defaultSort('number')
            ->orderBy('id')
            ->get();
    }

    public function findByMovieIdAndNumber(int $movieId, int $number, bool $forUpdate = false): ?Season
    {
        $query = Season::query()
            ->where('movie_id', $movieId)
            ->where('number', $number);

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function create(SeasonCreateData $dto): Season
    {
        return Season::create([
            'movie_id' => $dto->movie_id,
            'kp_id' => $dto->kp_id,
            'kp_season_id' => $dto->kp_season_id,
            'kp_movie_id' => $dto->kp_movie_id,
            'name' => $dto->name,
            'en_name' => $dto->en_name,
            'number' => $dto->number,
            'air_date' => $dto->air_date,
            'episodes_count' => $dto->episodes_count,
            'duration' => $dto->duration,
            'poster' => $dto->poster,
            'poster_preview' => $dto->poster_preview,
        ]);
    }

    public function update(Season $season, SeasonUpdateData $dto): Season
    {
        $attributes = [];

        foreach ($dto->toArray() as $key => $value) {
            if ($value instanceof Optional) {
                continue;
            }

            $attributes[$key] = $value;
        }

        if ($attributes !== []) {
            $season->update($attributes);
        }

        return $season->refresh();
    }

    public function delete(Season $season): bool
    {
        return (bool) $season->delete();
    }
}
