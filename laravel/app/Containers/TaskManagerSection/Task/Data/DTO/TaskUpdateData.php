<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Optional;

class TaskUpdateData extends Data
{
    public int|Optional|null $task_list_id;
    public int|Optional|null $parent_id;
    public string|Optional|null $title;
    public string|Optional|null $content;
    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d H:i:s')]
    public Carbon|Optional|null $finished_at;
    public bool|Optional|null $is_declined;
    public bool|Optional|null $is_favorite;
    public int|Optional|null $priority;

    public function __construct(
    ) {
    }
}
