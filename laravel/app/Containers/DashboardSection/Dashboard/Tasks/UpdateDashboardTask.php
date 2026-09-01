<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\Tasks;

use App\Containers\DashboardSection\Dashboard\Data\DTO\UpdateDashboardDto;
use App\Containers\DashboardSection\Dashboard\Data\Repositories\DashboardRepository;
use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Spatie\LaravelData\Optional;

class UpdateDashboardTask extends ParentTask
{
    public function __construct(
        private readonly DashboardRepository $dashboardRepository,
    ) {
    }

    public function run(Dashboard $dashboard, UpdateDashboardDto $dto): Dashboard
    {
        if (!($dto->is_default instanceof Optional) && $dto->is_default === true) {
            $this->dashboardRepository->clearDefaultExcept($dashboard->id, (int) $dashboard->user_id);
        }

        return $this->dashboardRepository->update($dashboard, $dto);
    }
}
