<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Data\Repositories;

use App\Containers\MusicSection\Artist\Data\DTO\CreateArtistDto;
use App\Containers\MusicSection\Artist\Data\DTO\UpdateArtistDto;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;

class ArtistRepository
{
    public function getWithCursor(): CursorPaginator
    {
        return QueryBuilder::for(Artist::class)
                           ->allowedFilters([
                               AllowedFilter::partial('name'),
                               AllowedFilter::exact('country_id'),
                           ])
                           ->allowedSorts(['name', 'created_at'])
                           ->allowedIncludes(['tags'])
                           ->with(['tags'])
                           ->orderByDesc('created_at')
                           ->cursorPaginate(12);
    }

    public function getWithPaginate(): LengthAwarePaginator
    {
        return QueryBuilder::for(Artist::class)
                           ->allowedFilters([
                               AllowedFilter::partial('name'),
                               AllowedFilter::exact('country_id'),
                           ])
                           ->allowedSorts(['name', 'created_at'])
                           ->with(['tags'])
                           ->orderByDesc('created_at')
                           ->paginate(100);
    }

    public function findByPath(string $path): ?Artist
    {
        return Artist::query()->where('path', $path)->first();
    }

    public function findByName(string $name): ?Artist
    {
        return Artist::query()->where('name', $name)->first();
    }

    public function create(CreateArtistDto $dto): Artist
    {
        return Artist::create([
            'user_id' => $dto->user_id,
            'name' => $dto->name,
            'description' => $dto->description,
            'country_id' => $dto->country_id,
            'path' => $dto->path,
            'image' => $dto->image,
        ]);
    }

    public function updateOrCreate(CreateArtistDto $dto): Artist
    {
        return Artist::updateOrCreate([
            'name' => $dto->name,
        ], [
            'user_id' => $dto->user_id,
            'description' => $dto->description,
            'country_id' => $dto->country_id,
            'path' => $dto->path,
            'image' => $dto->image,
        ]);
    }

    public function update(Artist $artist, UpdateArtistDto $dto): Artist
    {
        $artist->update($this->payload($dto->toArray(), ['user_id']));

        return $artist;
    }

    public function delete(Artist $artist): ?bool
    {
        return $artist->delete();
    }

    /**
     * Synchronize albums for artist
     *
     * @param Artist $artist
     * @param array $albumIds
     * @return array
     */
    public function syncAlbumsWithoutDetaching(Artist $artist, array $albumIds): array
    {
        return $artist->albums()->syncWithoutDetaching($albumIds);
    }

    private function payload(array $data, array $except = []): array
    {
        return collect($data)
            ->except($except)
            ->filter(fn (mixed $value) => $value !== null)
            ->all();
    }
}
