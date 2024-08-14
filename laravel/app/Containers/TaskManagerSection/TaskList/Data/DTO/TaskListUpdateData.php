<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Data\DTO;

use Spatie\LaravelData\Data;

class TaskListUpdateData extends Data
{
    public string $title;

    public function __construct(
    ) {
    }
}
