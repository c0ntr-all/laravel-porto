<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Data\DTO;

use App\Ship\Parents\DTO\Data;

class TaskListUpdateData extends Data
{
    public string $title;

    public function __construct(
    ) {
    }
}
