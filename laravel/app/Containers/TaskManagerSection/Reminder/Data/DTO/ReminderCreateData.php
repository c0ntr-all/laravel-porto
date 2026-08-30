<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class ReminderCreateData extends Data
{
    public int $user_id;
    public int $task_id;
    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d H:i')]
    public Carbon $datetime;
    public ?int $interval_value = null;
    public ?string $interval_unit = null;
    public ?int $to_remind_before_value = null;
    public ?string $to_remind_before_unit = null;
    public bool $is_active = true;

    public function __construct(
    ) {
    }
}
