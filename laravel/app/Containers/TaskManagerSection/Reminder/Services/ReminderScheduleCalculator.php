<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Services;

use App\Containers\TaskManagerSection\Reminder\Enums\ReminderTimeUnitEnum;
use Illuminate\Support\Carbon;

class ReminderScheduleCalculator
{
    public function calculateNextRemindAt(
        Carbon $eventAt,
        ?int $remindBeforeValue,
        ReminderTimeUnitEnum|string|null $remindBeforeUnit
    ): Carbon {
        if ($remindBeforeValue === null || $remindBeforeValue < 1 || $remindBeforeUnit === null) {
            return $eventAt->copy();
        }

        $unit = $remindBeforeUnit instanceof ReminderTimeUnitEnum
            ? $remindBeforeUnit
            : ReminderTimeUnitEnum::fromString((string) $remindBeforeUnit);

        return $eventAt->copy()->sub($remindBeforeValue, $unit->carbonUnit());
    }

    public function advanceEventAt(
        Carbon $eventAt,
        int $intervalValue,
        ReminderTimeUnitEnum|string $intervalUnit
    ): Carbon {
        $unit = $intervalUnit instanceof ReminderTimeUnitEnum
            ? $intervalUnit
            : ReminderTimeUnitEnum::fromString((string) $intervalUnit);

        return $eventAt->copy()->add($intervalValue, $unit->carbonUnit());
    }

    public function hasRecurrence(?int $intervalValue, ReminderTimeUnitEnum|string|null $intervalUnit): bool
    {
        return $intervalValue !== null
            && $intervalValue >= 1
            && $intervalUnit !== null
            && $intervalUnit !== '';
    }
}
