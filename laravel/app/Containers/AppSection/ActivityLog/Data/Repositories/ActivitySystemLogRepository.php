<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Data\Repositories;

use App\Containers\AppSection\ActivityLog\Models\ActivitySystemLog;
use Illuminate\Database\Eloquent\Collection;

class ActivitySystemLogRepository
{
    public function getByCorrelationUuid(string $uuid): Collection
    {
        return ActivitySystemLog::query()
            ->where('correlation_uuid', $uuid)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    public function create(array $data): ActivitySystemLog
    {
        return ActivitySystemLog::create($data);
    }
}
