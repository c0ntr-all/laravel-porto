<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Tasks;

use App\Containers\AppSection\ActivityLog\Data\Repositories\ActivitySystemLogRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListSystemLogsByUuid extends ParentTask
{
    public function __construct(
        private readonly ActivitySystemLogRepository $activitySystemLogRepository
    )
    {
    }

    /**
     * @param string $uuid
     * @return Collection
     */
    public function run(string $uuid): Collection
    {
        return $this->activitySystemLogRepository->getByCriteria([
            'correlation_uuid' => $uuid
        ]);
    }
}
