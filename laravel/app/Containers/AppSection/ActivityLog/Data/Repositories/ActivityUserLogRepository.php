<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Data\Repositories;

use App\Containers\AppSection\ActivityLog\Models\ActivityUseCaseLog;

class ActivityUserLogRepository
{
    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data): ActivityUseCaseLog
    {
        return ActivityUseCaseLog::create($data);
    }
}
