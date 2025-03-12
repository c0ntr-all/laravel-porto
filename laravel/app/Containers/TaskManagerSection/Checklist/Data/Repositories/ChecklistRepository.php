<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Data\Repositories;

use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistCreateData;
use App\Containers\TaskManagerSection\Checklist\Data\DTO\ChecklistUpdateData;
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

    /**
     * @param Checklist $checklist
     * @param ChecklistUpdateData $dto
     * @return Checklist
     */
    public function update(Checklist $checklist, ChecklistUpdateData $dto): Checklist
    {
        $checklist->update($dto->toArray());

        return $checklist;
    }
}
