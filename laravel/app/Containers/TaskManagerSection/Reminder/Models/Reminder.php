<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\TaskManagerSection\Reminder\Enums\ReminderTimeUnitEnum;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $task_id
 * @property \Illuminate\Support\Carbon|null $datetime
 * @property int|null $interval_value
 * @property ReminderTimeUnitEnum|null $interval_unit
 * @property int|null $to_remind_before_value
 * @property ReminderTimeUnitEnum|null $to_remind_before_unit
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $next_remind_at
 * @property \Illuminate\Support\Carbon|null $last_reminded_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class Reminder extends Model
{
    use HasUser;

    protected $table = 'tm_reminders';

    protected $fillable = [
        'user_id',
        'task_id',
        'datetime',
        'interval_value',
        'interval_unit',
        'to_remind_before_value',
        'to_remind_before_unit',
        'is_active',
        'next_remind_at',
        'last_reminded_at',
    ];

    protected $casts = [
        'datetime' => 'datetime',
        'next_remind_at' => 'datetime',
        'last_reminded_at' => 'datetime',
        'is_active' => 'bool',
        'interval_value' => 'integer',
        'to_remind_before_value' => 'integer',
        'interval_unit' => ReminderTimeUnitEnum::class,
        'to_remind_before_unit' => ReminderTimeUnitEnum::class,
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function scopeDue(Builder $query, ?\DateTimeInterface $at = null): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereNotNull('next_remind_at')
            ->where('next_remind_at', '<=', $at ?? now());
    }

    public function hasRecurrence(): bool
    {
        return $this->interval_value !== null
            && $this->interval_value >= 1
            && $this->interval_unit !== null;
    }
}
