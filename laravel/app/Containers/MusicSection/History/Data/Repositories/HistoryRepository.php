<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\Data\Repositories;

use App\Containers\MusicSection\History\Data\DTO\CreateHistoryDto;
use App\Containers\MusicSection\History\Data\DTO\UpdateHistoryDto;
use App\Containers\MusicSection\History\Models\History;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Pagination\CursorPaginator;
use Spatie\QueryBuilder\AllowedFilter;

class HistoryRepository
{
    public function getWithCursor(int $userId): CursorPaginator
    {
        return QueryBuilder::for(History::class)
                           ->where('user_id', $userId)
                           ->allowedFilters([
                               AllowedFilter::exact('track_id'),
                           ])
                           ->allowedSorts(['created_at', 'updated_at'])
                           ->allowedIncludes(['track'])
                           ->with(['track'])
                           ->orderByDesc('created_at')
                           ->cursorPaginate(50);
    }

    public function create(CreateHistoryDto $dto): History
    {
        return History::create([
            'user_id' => $dto->user_id,
            'track_id' => $dto->track_id,
        ]);
    }

    public function update(History $history, UpdateHistoryDto $dto): History
    {
        $history->update(collect($dto->toArray())->filter(fn (mixed $value) => $value !== null)->all());

        return $history;
    }

    public function delete(History $history): ?bool
    {
        return $history->delete();
    }
}
