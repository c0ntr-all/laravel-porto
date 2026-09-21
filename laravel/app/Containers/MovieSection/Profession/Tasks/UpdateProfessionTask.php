<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\Tasks;

use App\Containers\MovieSection\Profession\Data\DTO\ProfessionUpdateData;
use App\Containers\MovieSection\Profession\Data\Repositories\ProfessionRepository;
use App\Containers\MovieSection\Profession\Models\Profession;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateProfessionTask extends ParentTask
{
    public function __construct(
        private readonly ProfessionRepository $professionRepository,
    ) {
    }

    public function run(Profession $profession, ProfessionUpdateData $dto): Profession
    {
        return $this->professionRepository->update($profession, $dto);
    }
}
