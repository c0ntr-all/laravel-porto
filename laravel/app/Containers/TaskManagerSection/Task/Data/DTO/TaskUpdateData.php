<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Data\DTO;

use Spatie\LaravelData\Data;

class TaskUpdateData extends Data
{
    public ?int $task_list_id = null;
    public string $title;
    public ?string $content = null;

    public function __construct(
    ) {
    }
}
