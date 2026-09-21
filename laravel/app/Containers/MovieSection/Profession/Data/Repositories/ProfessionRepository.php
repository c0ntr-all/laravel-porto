<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\Data\Repositories;

use App\Containers\MovieSection\Profession\Data\DTO\ProfessionCreateData;
use App\Containers\MovieSection\Profession\Data\DTO\ProfessionUpdateData;
use App\Containers\MovieSection\Profession\Models\Profession;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;
use Spatie\QueryBuilder\AllowedFilter;

class ProfessionRepository
{
    public function get(): Collection
    {
        return QueryBuilder::for(Profession::class)
            ->allowedFilters([
                AllowedFilter::partial('en_name'),
                AllowedFilter::partial('name'),
            ])
            ->allowedSorts(['en_name', 'name', 'created_at'])
            ->defaultSort('en_name')
            ->get();
    }

    public function firstOrCreateByEnName(string $enName, ?string $name = null): Profession
    {
        $profession = Profession::query()->where('en_name', $enName)->first();

        if ($profession === null) {
            return $this->create(ProfessionCreateData::from([
                'en_name' => $enName,
                'name' => $name,
            ]));
        }

        if ($name !== null && $name !== '' && ($profession->name === null || $profession->name === '')) {
            $profession->update(['name' => $name]);
        }

        return $profession;
    }

    public function create(ProfessionCreateData $dto): Profession
    {
        return Profession::create([
            'en_name' => $dto->en_name,
            'name' => $dto->name,
        ]);
    }

    public function update(Profession $profession, ProfessionUpdateData $dto): Profession
    {
        $attributes = [];

        foreach ($dto->toArray() as $key => $value) {
            if ($value instanceof Optional) {
                continue;
            }

            $attributes[$key] = $value;
        }

        if ($attributes !== []) {
            $profession->update($attributes);
        }

        return $profession->refresh();
    }

    public function delete(Profession $profession): bool
    {
        return (bool) $profession->delete();
    }
}
