<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Data\DTO;

use App\Ship\Parents\DTO\Data;

class TaskCreateData extends Data
{
    public int $user_id;
    public ?int $task_list_id = null;
    public ?int $parent_id = null;
    public string $title;
    public ?string $content = null;
    public bool $is_favorite = false;
    public ?int $priority = null;
    public ?array $attachments = [];

    public function __construct(
    ) {
    }
}
