<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Data\Repositories;

use App\Containers\GameSection\Import\Data\DTO\GameImportCreateData;
use App\Containers\GameSection\Import\Models\GameImport;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Pagination\CursorPaginator;
use Spatie\QueryBuilder\AllowedFilter;

class GameImportRepository
{
    public function create(GameImportCreateData $dto): GameImport
    {
        return GameImport::query()->create([
            'user_id' => $dto->user_id,
            'steam_id' => $dto->steam_id,
            'source_url' => $dto->source_url,
            'status' => $dto->status,
            'started_at' => now(),
        ]);
    }

    public function save(GameImport $import): GameImport
    {
        $import->save();

        return $import->refresh();
    }

    public function getWithCursor(int $userId): CursorPaginator
    {
        return QueryBuilder::for(GameImport::query()->where('user_id', $userId), request())
            ->allowedFilters([
                AllowedFilter::exact('steam_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('game_id'),
            ])
            ->allowedSorts(['created_at', 'finished_at', 'steam_id'])
            ->allowedIncludes(['game'])
            ->with(['game'])
            ->defaultSort('-created_at')
            ->orderByDesc('id')
            ->cursorPaginate(24);
    }
}
