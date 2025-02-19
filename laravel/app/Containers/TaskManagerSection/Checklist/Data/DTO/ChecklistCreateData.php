<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Data\DTO;

use App\Ship\Parents\DTO\Data;

class ChecklistCreateData extends Data
{
    public int $user_id;
    public int $task_id;
    public string $title;

    public function __construct(
    ) {
    }
}
