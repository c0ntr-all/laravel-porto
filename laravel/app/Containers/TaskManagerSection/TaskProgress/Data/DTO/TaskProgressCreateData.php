<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskProgress\Data\DTO;

use App\Ship\Parents\DTO\Data;

class TaskProgressCreateData extends Data
{
    public int $user_id;
    public int $task_id;
    public string $content;
    public bool $is_final = false;

    public function __construct(
    ) {
    }
}
