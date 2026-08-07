<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\Data\DTO;

use App\Ship\Parents\DTO\Data;

class TaskTemplateCreateData extends Data
{
    public int $user_id;
    public string $title;
    public ?string $content = null;
    /** @var array<int, array{title: string, items?: array<int, array{title: string}>}>|null */
    public ?array $checklists = null;

    public function __construct(
    ) {
    }
}
