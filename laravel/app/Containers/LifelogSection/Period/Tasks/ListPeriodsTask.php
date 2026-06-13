<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\Tasks;

use App\Containers\LifelogSection\Period\Data\DTO\PeriodListDto;
use App\Containers\LifelogSection\Period\Data\Repositories\PeriodRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListPeriodsTask extends ParentTask
{
    public function __construct(
        private readonly PeriodRepository $periodRepository
    )
    {
    }

    public function run(PeriodListDto $dto): Collection
    {
        return $this->periodRepository->get([
            'user_id' => $dto->user_id,
        ]);
    }
}
