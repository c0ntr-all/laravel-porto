<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Data\Repositories;

use App\Containers\MovieSection\Import\Data\DTO\MovieImportCreateData;
use App\Containers\MovieSection\Import\Models\MovieImport;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Pagination\CursorPaginator;
use Spatie\QueryBuilder\AllowedFilter;

class MovieImportRepository
{
    public function create(MovieImportCreateData $dto): MovieImport
    {
        return MovieImport::create([
            'user_id' => $dto->user_id,
            'kp_id' => $dto->kp_id,
            'source_url' => $dto->source_url,
            'status' => $dto->status,
            'started_at' => now(),
        ]);
    }

    public function save(MovieImport $import): MovieImport
    {
        $import->save();

        return $import->refresh();
    }

    public function getWithCursor(int $userId): CursorPaginator
    {
        return QueryBuilder::for(MovieImport::query()->where('user_id', $userId), request())
            ->allowedFilters([
                AllowedFilter::exact('kp_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('movie_id'),
            ])
            ->allowedSorts(['created_at', 'finished_at', 'kp_id'])
            ->allowedIncludes(['movie', 'movie.genres', 'movie.countries'])
            ->with(['movie.genres', 'movie.countries'])
            ->defaultSort('-created_at')
            ->orderByDesc('id')
            ->cursorPaginate(24);
    }
}
