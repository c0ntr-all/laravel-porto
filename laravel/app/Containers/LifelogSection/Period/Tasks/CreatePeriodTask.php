<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\Tasks;

use App\Containers\LifelogSection\Period\Data\DTO\PeriodCreateDto;
use App\Containers\LifelogSection\Period\Data\Repositories\PeriodRepository;
use App\Containers\LifelogSection\Period\Models\Period;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreatePeriodTask extends ParentTask
{
    public function __construct(
        private readonly PeriodRepository $periodRepository
    )
    {
    }

    public function run(PeriodCreateDto $dto): Period
    {
        return $this->periodRepository
            ->createPeriod($dto)
            ->load(['startPost', 'endPost']);
    }
}
