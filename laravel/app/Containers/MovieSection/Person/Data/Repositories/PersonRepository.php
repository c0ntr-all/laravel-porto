<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\Data\Repositories;

use App\Containers\MovieSection\Person\Data\DTO\PersonCreateData;
use App\Containers\MovieSection\Person\Data\DTO\PersonUpdateData;
use App\Containers\MovieSection\Person\Models\Person;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\CursorPaginator;
use Spatie\LaravelData\Optional;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;

class PersonRepository
{
    public function getWithCursor(): CursorPaginator
    {
        return QueryBuilder::for(Person::class, request())
            ->allowedFilters($this->allowedFilters())
            ->allowedSorts(['name', 'en_name', 'created_at'])
            ->allowedIncludes([
                AllowedInclude::relationship('profession'),
                AllowedInclude::relationship('professions'),
                AllowedInclude::relationship('movies'),
            ])
            ->with(['profession'])
            ->defaultSort('name')
            ->orderBy('id')
            ->cursorPaginate(24);
    }

    public function findByKpId(int $kpId, bool $forUpdate = false): ?Person
    {
        $query = Person::query()->where('kp_id', $kpId);
        if ($forUpdate) {
            $query->lockForUpdate();
        }

        return $query->first()?->load(['profession', 'professions']);
    }

    public function create(PersonCreateData $dto): Person
    {
        $person = Person::create([
            'kp_id' => $dto->kp_id,
            'profession_id' => $dto->profession_id,
            'name' => $dto->name,
            'en_name' => $dto->en_name,
            'photo' => $dto->photo,
        ]);
        $person->professions()->syncWithoutDetaching([$dto->profession_id]);

        return $person->load(['profession', 'professions']);
    }

    public function update(Person $person, PersonUpdateData $dto): Person
    {
        $attributes = [];

        foreach ($dto->toArray() as $key => $value) {
            if ($value instanceof Optional) {
                continue;
            }

            $attributes[$key] = $value;
        }

        if ($attributes !== []) {
            $person->update($attributes);
        }

        if (array_key_exists('profession_id', $attributes) && is_int($attributes['profession_id'])) {
            $person->professions()->syncWithoutDetaching([$attributes['profession_id']]);
        }

        return $person->refresh()->load(['profession', 'professions']);
    }

    public function delete(Person $person): bool
    {
        return (bool) $person->delete();
    }

    /**
     * @return list<AllowedFilter>
     */
    private function allowedFilters(): array
    {
        return [
            AllowedFilter::callback('name', function (Builder $query, mixed $value): void {
                $term = trim((string) $value);
                if ($term === '') {
                    return;
                }

                $query->where(function (Builder $inner) use ($term): void {
                    $inner->where('name', 'like', '%'.$term.'%')
                        ->orWhere('en_name', 'like', '%'.$term.'%');
                });
            }),
            AllowedFilter::exact('profession_id'),
            AllowedFilter::callback('profession', function (Builder $query, mixed $value): void {
                $term = trim((string) $value);
                if ($term === '') {
                    return;
                }

                $query->where(function (Builder $inner) use ($term): void {
                    $inner->whereHas('profession', function (Builder $profession) use ($term): void {
                        $profession->where('en_name', 'like', '%'.$term.'%')
                            ->orWhere('name', 'like', '%'.$term.'%');
                    })->orWhereHas('professions', function (Builder $profession) use ($term): void {
                        $profession->where('en_name', 'like', '%'.$term.'%')
                            ->orWhere('name', 'like', '%'.$term.'%');
                    });
                });
            }),
        ];
    }
}
