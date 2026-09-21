<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Data\Repositories;

use App\Containers\MovieSection\Folder\Data\DTO\FolderCreateData;
use App\Containers\MovieSection\Folder\Data\DTO\FolderUpdateData;
use App\Containers\MovieSection\Folder\Exceptions\SystemFolderProtectedException;
use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Folder\Support\FolderMoviesCountCache;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Optional;
use Spatie\QueryBuilder\AllowedSort;

class FolderRepository
{
    public function listForUser(int $userId): Collection
    {
        return Folder::query()
            ->where('user_id', $userId)
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }

    public function findOwnedById(int $folderId, int $userId): Folder
    {
        $folder = Folder::query()
            ->where('id', $folderId)
            ->where('user_id', $userId)
            ->first();

        if (!$folder) {
            throw (new ModelNotFoundException())->setModel(Folder::class, [$folderId]);
        }

        return $folder;
    }

    public function create(FolderCreateData $dto): Folder
    {
        return Folder::create([
            'user_id' => $dto->user_id,
            'name' => $dto->name,
            'slug' => null,
            'is_system' => false,
            'movies_count' => 0,
        ]);
    }

    public function update(Folder $folder, FolderUpdateData $dto): Folder
    {
        if ($folder->is_system) {
            throw new SystemFolderProtectedException('System folders cannot be renamed.');
        }

        $attributes = [];

        foreach ($dto->toArray() as $key => $value) {
            if ($value instanceof Optional) {
                continue;
            }

            $attributes[$key] = $value;
        }

        if ($attributes !== []) {
            $folder->update($attributes);
        }

        return $folder->refresh();
    }

    public function delete(Folder $folder): bool
    {
        if ($folder->is_system) {
            throw new SystemFolderProtectedException('System folders cannot be deleted.');
        }

        FolderMoviesCountCache::forget((int) $folder->id);

        return (bool) $folder->delete();
    }

    public function attachMovie(Folder $folder, Movie $movie, ?Carbon $addedAt = null): Folder
    {
        if ($folder->movies()->where('movies.id', $movie->id)->exists()) {
            return $folder->refresh();
        }

        $folder->movies()->attach($movie->id, [
            'added_at' => $addedAt ?? now(),
        ]);

        FolderMoviesCountCache::increment($folder);

        return $folder->refresh();
    }

    public function detachMovie(Folder $folder, Movie $movie): Folder
    {
        $detached = $folder->movies()->detach($movie->id);

        if ($detached > 0) {
            FolderMoviesCountCache::decrement($folder);
        }

        return $folder->refresh();
    }

    public function listMovies(Folder $folder): LengthAwarePaginator
    {
        $perPage = min(100, max(1, (int) request('per_page', 24)));

        return QueryBuilder::for(
            Movie::query()
                ->select('movies.*')
                ->addSelect('movie_folder_movie.added_at as folder_added_at')
                ->join('movie_folder_movie', 'movie_folder_movie.movie_id', '=', 'movies.id')
                ->where('movie_folder_movie.folder_id', $folder->id),
            request(),
        )
            ->allowedSorts([
                AllowedSort::field('added_at', 'movie_folder_movie.added_at'),
                AllowedSort::field('title', 'movies.title'),
                AllowedSort::field('year', 'movies.year'),
                AllowedSort::field('kp_rating', 'movies.kp_rating'),
            ])
            ->defaultSort('-added_at')
            ->paginate($perPage)
            ->appends(request()->query());
    }
}
