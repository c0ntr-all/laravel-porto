<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Data\DTO;

use Spatie\LaravelData\Data;

class TaskCreateData extends Data
{
    public int $user_id;
    public ?int $task_list_id = null;
    public string $title;
    public ?string $content = null;

    public function __construct(
    ) {
    }
}
