<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Tasks\Task
 *
 * @property int $id
 * @property int $user_id
 * @property int $task_id
 * @property \Illuminate\Support\Carbon|null $datetime
 * @property string $to_remind_before
 * @property string $interval
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task query()
 * @mixin \Eloquent
 */
class Reminder extends Model
{
    use HasUser;

    protected $table = 'tm_reminders';

    protected $fillable = [
        'id',
        'user_id',
        'task_id',
        'datetime',
        'to_remind_before',
        'interval',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'datetime' => 'datetime'
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
