<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\Data\Repositories;

use App\Containers\MovieSection\Genre\Data\DTO\GenreCreateData;
use App\Containers\MovieSection\Genre\Data\DTO\GenreUpdateData;
use App\Containers\MovieSection\Genre\Models\Genre;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Spatie\LaravelData\Optional;
use Spatie\QueryBuilder\AllowedFilter;

class GenreRepository
{
    public function get(): Collection
    {
        return QueryBuilder::for(Genre::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::exact('kp_id'),
                AllowedFilter::exact('slug'),
            ])
            ->allowedSorts(['name', 'created_at'])
            ->defaultSort('name')
            ->get();
    }

    public function firstOrCreateByName(string $name, ?int $kpId = null): Genre
    {
        $genre = Genre::query()->where('name', $name)->first();

        if ($genre === null) {
            return $this->create(GenreCreateData::from([
                'name' => $name,
                'kp_id' => $kpId,
            ]));
        }

        if ($kpId !== null && $genre->kp_id === null) {
            $genre->update(['kp_id' => $kpId]);
        }

        return $genre;
    }

    public function create(GenreCreateData $dto): Genre
    {
        $slug = $dto->slug ?: Str::slug($dto->name);
        if ($slug === '') {
            $slug = 'genre-'.substr(sha1($dto->name), 0, 12);
        }

        return Genre::create([
            'kp_id' => $dto->kp_id,
            'name' => $dto->name,
            'slug' => $slug,
        ]);
    }

    public function update(Genre $genre, GenreUpdateData $dto): Genre
    {
        $attributes = [];

        foreach ($dto->toArray() as $key => $value) {
            if ($value instanceof Optional) {
                continue;
            }

            $attributes[$key] = $value;
        }

        if (array_key_exists('name', $attributes) && !array_key_exists('slug', $attributes)) {
            $attributes['slug'] = Str::slug($attributes['name']);
        }

        if ($attributes !== []) {
            $genre->update($attributes);
        }

        return $genre->refresh();
    }

    public function delete(Genre $genre): bool
    {
        return (bool) $genre->delete();
    }
}
