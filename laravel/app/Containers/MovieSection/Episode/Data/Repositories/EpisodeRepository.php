<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\Data\Repositories;

use App\Containers\MovieSection\Episode\Data\DTO\EpisodeCreateData;
use App\Containers\MovieSection\Episode\Data\DTO\EpisodeUpdateData;
use App\Containers\MovieSection\Episode\Models\Episode;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;

class EpisodeRepository
{
    public function get(): Collection
    {
        return QueryBuilder::for(Episode::class, request())
            ->allowedFilters([
                AllowedFilter::exact('season_id'),
                AllowedFilter::exact('number'),
                AllowedFilter::exact('kp_id'),
                AllowedFilter::exact('kp_season_id'),
            ])
            ->allowedSorts(['number', 'air_date', 'created_at'])
            ->allowedIncludes([
                AllowedInclude::relationship('season'),
            ])
            ->defaultSort('number')
            ->orderBy('id')
            ->get();
    }

    public function findBySeasonIdAndNumber(int $seasonId, int $number, bool $forUpdate = false): ?Episode
    {
        $query = Episode::query()
            ->where('season_id', $seasonId)
            ->where('number', $number);

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function create(EpisodeCreateData $dto): Episode
    {
        return Episode::create([
            'season_id' => $dto->season_id,
            'kp_id' => $dto->kp_id,
            'kp_season_id' => $dto->kp_season_id,
            'name' => $dto->name,
            'description' => $dto->description,
            'en_description' => $dto->en_description,
            'number' => $dto->number,
            'duration' => $dto->duration,
            'air_date' => $dto->air_date,
            'still' => $dto->still,
            'still_preview' => $dto->still_preview,
        ]);
    }

    public function update(Episode $episode, EpisodeUpdateData $dto): Episode
    {
        $attributes = [];

        foreach ($dto->toArray() as $key => $value) {
            if ($value instanceof Optional) {
                continue;
            }

            $attributes[$key] = $value;
        }

        if ($attributes !== []) {
            $episode->update($attributes);
        }

        return $episode->refresh();
    }

    public function delete(Episode $episode): bool
    {
        return (bool) $episode->delete();
    }
}
