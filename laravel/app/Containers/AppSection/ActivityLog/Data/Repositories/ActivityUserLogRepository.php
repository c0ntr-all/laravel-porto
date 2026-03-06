<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Data\Repositories;

use App\Containers\AppSection\ActivityLog\Models\ActivityUseCaseLog;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;

class ActivityUserLogRepository
{
    public function get(array $data = []): Collection
    {
        return QueryBuilder::for(ActivityUseCaseLog::class)
            ->allowedSorts('created_at')
            ->allowedFilters([
                // Явно указываем, что это фильтр точного совпадения
                AllowedFilter::exact('loggable_id'),
                AllowedFilter::exact('loggable_type'),
            ])
            ->with(['user'])
            ->get();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data): ActivityUseCaseLog
    {
        return ActivityUseCaseLog::create($data);
    }
}
