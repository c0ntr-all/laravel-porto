<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Data\Repositories;

use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistCreateData;
use App\Containers\TaskManagerSection\Checklist\Models\Checklist;

class ChecklistRepository
{
    /**
     * @param ChecklistCreateData $dto
     * @return mixed
     */
    public function create(ChecklistCreateData $dto): Checklist
    {
        return Checklist::create($dto->toArray());
    }
}
