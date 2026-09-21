<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\Tasks;

use App\Containers\MovieSection\Person\Data\Repositories\PersonRepository;
use App\Containers\MovieSection\Person\Models\Person;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeletePersonTask extends ParentTask
{
    public function __construct(
        private readonly PersonRepository $personRepository,
    ) {
    }

    public function run(Person $person): bool
    {
        return $this->personRepository->delete($person);
    }
}
