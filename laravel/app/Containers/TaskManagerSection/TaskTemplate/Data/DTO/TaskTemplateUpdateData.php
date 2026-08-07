<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class TaskTemplateUpdateData extends Data
{
    public string|Optional $title;
    public string|Optional|null $content;
    /** @var array<int, array{title: string, items?: array<int, array{title: string}>}>|Optional */
    public array|Optional $checklists;

    public function __construct(
    ) {
    }
}
