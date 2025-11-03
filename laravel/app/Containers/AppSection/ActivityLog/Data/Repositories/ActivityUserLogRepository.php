<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Data\Repositories;

use App\Containers\AppSection\ActivityLog\Models\ActivityUserLog;

class ActivityUserLogRepository
{
    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data): ActivityUserLog
    {
        return ActivityUserLog::create($data);
    }
}
