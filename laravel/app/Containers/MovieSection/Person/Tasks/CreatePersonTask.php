<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\Tasks;

use App\Containers\MovieSection\Person\Data\DTO\PersonCreateData;
use App\Containers\MovieSection\Person\Data\Repositories\PersonRepository;
use App\Containers\MovieSection\Person\Models\Person;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreatePersonTask extends ParentTask
{
    public function __construct(
        private readonly PersonRepository $personRepository,
    ) {
    }

    public function run(PersonCreateData $dto): Person
    {
        return $this->personRepository->create($dto);
    }
}
