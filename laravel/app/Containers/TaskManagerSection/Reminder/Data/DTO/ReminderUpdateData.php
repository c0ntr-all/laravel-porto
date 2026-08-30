<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Optional;

class ReminderUpdateData extends Data
{
    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d H:i')]
    public Carbon|Optional $datetime;
    public int|Optional|null $interval_value;
    public string|Optional|null $interval_unit;
    public int|Optional|null $to_remind_before_value;
    public string|Optional|null $to_remind_before_unit;
    public bool|Optional $is_active;

    public function __construct(
    ) {
    }
}
