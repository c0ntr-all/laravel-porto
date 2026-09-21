<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\Tasks;

use App\Containers\MovieSection\Profession\Data\Repositories\ProfessionRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListProfessionsTask extends ParentTask
{
    public function __construct(
        private readonly ProfessionRepository $professionRepository,
    ) {
    }

    public function run(): Collection
    {
        return $this->professionRepository->get();
    }
}
