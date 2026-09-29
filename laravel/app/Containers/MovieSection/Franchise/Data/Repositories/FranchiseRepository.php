<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\Data\Repositories;

use App\Containers\MovieSection\Franchise\Data\DTO\FranchiseCreateData;
use App\Containers\MovieSection\Franchise\Data\DTO\FranchiseUpdateData;
use App\Containers\MovieSection\Franchise\Models\Franchise;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\LaravelData\Optional;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class FranchiseRepository
{
    public function get(): Collection
    {
        return QueryBuilder::for(Franchise::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::exact('user_id'),
            ])
            ->allowedSorts(['name', 'order', 'created_at'])
            ->defaultSort('order')
            ->get();
    }

    public function create(FranchiseCreateData $dto): Franchise
    {
        $order = $dto->order;

        if ($order === null) {
            $order = (int) Franchise::query()->max('order') + 1;
        }

        return Franchise::create([
            'user_id' => $dto->user_id,
            'name' => $dto->name,
            'description' => $dto->description,
            'image' => $dto->image,
            'order' => $order,
        ]);
    }

    public function update(Franchise $franchise, FranchiseUpdateData $dto): Franchise
    {
        $attributes = [];

        foreach ($dto->toArray() as $key => $value) {
            if ($value instanceof Optional) {
                continue;
            }

            $attributes[$key] = $value;
        }

        if ($attributes !== []) {
            $franchise->update($attributes);
        }

        return $franchise->refresh();
    }

    public function delete(Franchise $franchise): bool
    {
        return (bool) $franchise->delete();
    }

    public function attachMovie(Franchise $franchise, Movie $movie, ?int $order = null): Franchise
    {
        if ($franchise->movies()->where('movies.id', $movie->id)->exists()) {
            if ($order !== null) {
                $franchise->movies()->updateExistingPivot($movie->id, [
                    'order' => $order,
                ]);
            }

            return $franchise->refresh();
        }

        if ($order === null) {
            $order = (int) $franchise->movies()->max('movie_franchise_movie.order') + 1;
        }

        $franchise->movies()->attach($movie->id, [
            'order' => $order,
        ]);

        return $franchise->refresh();
    }

    public function updateMovieOrder(Franchise $franchise, Movie $movie, int $order): Franchise
    {
        if (!$franchise->movies()->where('movies.id', $movie->id)->exists()) {
            abort(404);
        }

        $franchise->movies()->updateExistingPivot($movie->id, [
            'order' => $order,
        ]);

        return $franchise->refresh();
    }

    public function detachMovie(Franchise $franchise, Movie $movie): Franchise
    {
        $franchise->movies()->detach($movie->id);

        return $franchise->refresh();
    }

    public function listMovies(Franchise $franchise): LengthAwarePaginator
    {
        $perPage = min(100, max(1, (int) request('per_page', 24)));

        return QueryBuilder::for(
            Movie::query()
                ->select('movies.*')
                ->addSelect('movie_franchise_movie.order as franchise_order')
                ->join('movie_franchise_movie', 'movie_franchise_movie.movie_id', '=', 'movies.id')
                ->where('movie_franchise_movie.franchise_id', $franchise->id),
            request(),
        )
            ->allowedSorts([
                AllowedSort::field('order', 'movie_franchise_movie.order'),
                AllowedSort::field('title', 'movies.title'),
                AllowedSort::field('year', 'movies.year'),
                AllowedSort::field('kp_rating', 'movies.kp_rating'),
            ])
            ->with([
                'genres',
                'countries',
                'folders' => Movie::constrainFoldersToCurrentUser(),
            ])
            ->defaultSort('order')
            ->paginate($perPage)
            ->appends(request()->query());
    }
}
