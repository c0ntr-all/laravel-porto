<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Data\Repositories;

use App\Containers\AppSection\ActivityLog\Models\ActivityUseCaseLog;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Spatie\QueryBuilder\AllowedFilter;

class ActivityUserLogRepository
{
    public function paginate(int $perPage, ?string $cursor): CursorPaginator
    {
        return QueryBuilder::for(ActivityUseCaseLog::class)
            ->allowedSorts('created_at')
            ->defaultSort('-created_at')
            ->allowedFilters([
                AllowedFilter::exact('loggable_id'),
                AllowedFilter::exact('loggable_type'),
                AllowedFilter::exact('correlation_uuid'),
                AllowedFilter::exact('event_type'),
            ])
            ->with(['user'])
            ->orderByDesc('id')
            ->cursorPaginate($perPage, ['*'], 'cursor', $cursor);
    }

    public function create(array $data): ActivityUseCaseLog
    {
        return ActivityUseCaseLog::create($data);
    }
}
