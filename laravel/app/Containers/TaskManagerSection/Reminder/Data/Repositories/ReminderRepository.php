<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Data\Repositories;

use App\Containers\TaskManagerSection\Reminder\Data\DTO\ReminderCreateData;
use App\Containers\TaskManagerSection\Reminder\Data\DTO\ReminderUpdateData;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\Reminder\Services\ReminderScheduleCalculator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Optional;

class ReminderRepository
{
    public function __construct(
        private readonly ReminderScheduleCalculator $scheduleCalculator
    ) {
    }

    public function list(): Collection
    {
        return Reminder::query()
            ->with('task')
            ->orderByDesc('datetime')
            ->get();
    }

    public function findDue(Carbon $at, int $limit = 100): Collection
    {
        return Reminder::withoutGlobalScopes()
            ->due($at)
            ->with(['task', 'user'])
            ->orderBy('next_remind_at')
            ->limit($limit)
            ->get();
    }

    public function create(ReminderCreateData $dto): Reminder
    {
        $attributes = $dto->toArray();
        $attributes['next_remind_at'] = $this->scheduleCalculator->calculateNextRemindAt(
            $dto->datetime,
            $dto->to_remind_before_value,
            $dto->to_remind_before_unit
        );

        if (!$dto->is_active) {
            $attributes['next_remind_at'] = null;
        }

        return Reminder::create($attributes);
    }

    public function update(Reminder $reminder, ReminderUpdateData $dto): Reminder
    {
        $attributes = [];

        if (!($dto->datetime instanceof Optional)) {
            $attributes['datetime'] = $dto->datetime;
        }
        if (!($dto->interval_value instanceof Optional)) {
            $attributes['interval_value'] = $dto->interval_value;
        }
        if (!($dto->interval_unit instanceof Optional)) {
            $attributes['interval_unit'] = $dto->interval_unit;
        }
        if (!($dto->to_remind_before_value instanceof Optional)) {
            $attributes['to_remind_before_value'] = $dto->to_remind_before_value;
        }
        if (!($dto->to_remind_before_unit instanceof Optional)) {
            $attributes['to_remind_before_unit'] = $dto->to_remind_before_unit;
        }
        if (!($dto->is_active instanceof Optional)) {
            $attributes['is_active'] = $dto->is_active;
        }

        if ($attributes !== []) {
            $reminder->fill($attributes);
        }

        $eventAt = $reminder->datetime;
        $isActive = $reminder->is_active;

        $reminder->next_remind_at = $isActive && $eventAt
            ? $this->scheduleCalculator->calculateNextRemindAt(
                $eventAt,
                $reminder->to_remind_before_value,
                $reminder->to_remind_before_unit
            )
            : null;

        $reminder->save();

        return $reminder->refresh();
    }

    public function delete(Reminder $reminder): bool
    {
        return (bool) $reminder->delete();
    }

    public function save(Reminder $reminder): Reminder
    {
        $reminder->save();

        return $reminder;
    }
}
