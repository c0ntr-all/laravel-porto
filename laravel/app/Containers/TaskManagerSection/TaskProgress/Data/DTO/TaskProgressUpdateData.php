<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskProgress\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class TaskProgressUpdateData extends Data
{
    public string|Optional $title;

    public function __construct(
    ) {
    }
}
