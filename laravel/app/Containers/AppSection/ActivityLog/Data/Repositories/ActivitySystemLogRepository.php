<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Data\Repositories;

use App\Containers\AppSection\ActivityLog\Models\ActivitySystemLog;
use Illuminate\Database\Eloquent\Collection;

class ActivitySystemLogRepository
{
    /**
     * @param array $criteria
     * @return Collection
     */
    public function getByCriteria(array $criteria): Collection
    {
        return ActivitySystemLog::where($criteria)
                                ->get();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data): ActivitySystemLog
    {
        return ActivitySystemLog::create($data);
    }
}
