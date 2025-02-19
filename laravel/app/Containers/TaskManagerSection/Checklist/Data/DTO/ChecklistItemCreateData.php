<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Data\DTO;

use App\Ship\Parents\DTO\Data;

class ChecklistItemCreateData extends Data
{
    public int $user_id;
    public int $checklist_id;
    public string $title;

    public function __construct(
    ) {
    }
}
