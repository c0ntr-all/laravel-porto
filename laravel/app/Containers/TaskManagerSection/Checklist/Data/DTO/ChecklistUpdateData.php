<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class ChecklistUpdateData extends Data
{
    public string|Optional $title;

    public function __construct(
    ) {
    }
}
