<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\TaskManagerSection\Reminder\Enums\ReminderOccurrenceStatusEnum;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $reminder_id
 * @property int $task_id
 * @property ReminderOccurrenceStatusEnum $status
 * @property \Illuminate\Support\Carbon $scheduled_at
 * @property \Illuminate\Support\Carbon|null $notified_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property bool $is_overdue
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class ReminderOccurrence extends Model
{
    use HasUser;

    protected $table = 'tm_reminder_occurrences';

    protected $fillable = [
        'user_id',
        'reminder_id',
        'task_id',
        'status',
        'scheduled_at',
        'notified_at',
        'completed_at',
        'is_overdue',
    ];

    protected $casts = [
        'status' => ReminderOccurrenceStatusEnum::class,
        'scheduled_at' => 'datetime',
        'notified_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_overdue' => 'bool',
    ];

    public function reminder(): BelongsTo
    {
        return $this->belongsTo(Reminder::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === ReminderOccurrenceStatusEnum::COMPLETED;
    }
}
