<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Data\DTO;

use Spatie\LaravelData\Data;

class TaskListCreateData extends Data
{
    public int $user_id;
    public string $title;

    public function __construct(
    ) {
    }
}
