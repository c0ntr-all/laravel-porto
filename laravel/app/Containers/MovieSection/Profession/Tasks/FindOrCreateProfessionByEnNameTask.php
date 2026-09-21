<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\Tasks;

use App\Containers\MovieSection\Profession\Data\Repositories\ProfessionRepository;
use App\Containers\MovieSection\Profession\Models\Profession;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FindOrCreateProfessionByEnNameTask extends ParentTask
{
    public function __construct(
        private readonly ProfessionRepository $professionRepository,
    ) {
    }

    public function run(string $enName, ?string $name = null): Profession
    {
        return $this->professionRepository->firstOrCreateByEnName($enName, $name);
    }
}
