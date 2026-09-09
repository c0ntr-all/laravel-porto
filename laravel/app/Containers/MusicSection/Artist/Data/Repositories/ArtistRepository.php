<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Data\Repositories;

use App\Containers\MusicSection\Tag\Data\Filters\ArtistTagsFilter;
use App\Containers\MusicSection\Artist\Data\DTO\CreateArtistDto;
use App\Containers\MusicSection\Artist\Data\DTO\UpdateArtistDto;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;

class ArtistRepository
{
    public const DEFAULT_PER_PAGE = 12;
    public const MAX_PER_PAGE = 100;

    public function getWithCursor(?int $perPage = null): CursorPaginator
    {
        $perPage = $this->normalizePerPage($perPage);

        return QueryBuilder::for(Artist::class)
                           ->allowedFilters($this->allowedFilters())
                           ->allowedSorts(['name', 'created_at'])
                           ->allowedIncludes(['tags'])
                           ->with(['tags'])
                           ->defaultSort('-created_at')
                           ->orderByDesc('id')
                           ->cursorPaginate($perPage)
                           ->withQueryString();
    }

    public function getWithPaginate(): LengthAwarePaginator
    {
        return QueryBuilder::for(Artist::class)
                           ->allowedFilters($this->allowedFilters())
                           ->allowedSorts(['name', 'created_at'])
                           ->with(['tags'])
                           ->orderByDesc('created_at')
                           ->paginate(100);
    }

    public function findByPath(string $path): ?Artist
    {
        return Artist::query()->where('path', $path)->first();
    }

    /**
     * @param list<string> $paths
     * @return list<string>
     */
    public function existingPaths(array $paths): array
    {
        if ($paths === []) {
            return [];
        }

        return Artist::query()
            ->whereIn('path', $paths)
            ->pluck('path')
            ->all();
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

    private function normalizePerPage(?int $perPage): int
    {
        if ($perPage === null || $perPage < 1) {
            return self::DEFAULT_PER_PAGE;
        }

        return min($perPage, self::MAX_PER_PAGE);
    }

    /**
     * @return list<AllowedFilter>
     */
    private function allowedFilters(): array
    {
        return [
            AllowedFilter::partial('name'),
            AllowedFilter::exact('country_id'),
            AllowedFilter::custom('tags', new ArtistTagsFilter()),
            AllowedFilter::callback('tags_match', function (Builder $query): void {
                unset($query);
            }),
            AllowedFilter::callback('tags_nested', function (Builder $query): void {
                unset($query);
            }),
        ];
    }
}
