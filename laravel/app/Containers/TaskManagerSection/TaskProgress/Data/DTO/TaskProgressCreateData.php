<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskProgress\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class TaskProgressCreateData extends Data
{
    public int $user_id;
    public int $task_id;
    public string $title;
    public string $content;
    public bool $is_final = false;
    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d H:i')]
    public Carbon $finished_at;

    public function __construct(
    ) {
    }
}
