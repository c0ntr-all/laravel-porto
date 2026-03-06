<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Tasks;

use App\Containers\AppSection\ActivityLog\Data\Repositories\ActivityUserLogRepository;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

class ListUseCaseLogsTask extends Task
{
    public function __construct(
        private readonly ActivityUserLogRepository $useCaseLogsRepository
    )
    {
    }

    public function run(): Collection
    {
        return $this->useCaseLogsRepository->get();
    }
}
