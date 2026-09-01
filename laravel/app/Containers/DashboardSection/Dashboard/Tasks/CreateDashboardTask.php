<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\Tasks;

use App\Containers\DashboardSection\Dashboard\Data\DTO\CreateDashboardDto;
use App\Containers\DashboardSection\Dashboard\Data\Repositories\DashboardRepository;
use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateDashboardTask extends ParentTask
{
    public function __construct(
        private readonly DashboardRepository $dashboardRepository,
    ) {
    }

    public function run(CreateDashboardDto $dto): Dashboard
    {
        if ($dto->is_default) {
            $this->dashboardRepository->clearDefaultExcept(null, $dto->user_id);
        }

        return $this->dashboardRepository->create($dto);
    }
}
