<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Data\DTO;

use App\Ship\Parents\DTO\Data;

class ReorderTasksData extends Data
{
    /** @var list<int> */
    public array $ids;

    public function __construct()
    {
    }
}
