<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Data\DTO;

use App\Ship\Parents\DTO\Data;

class TaskUpdateData extends Data
{
    public ?int $task_list_id;
    public ?string $title;
    public ?string $content;

    public function __construct(
    ) {
    }
}
