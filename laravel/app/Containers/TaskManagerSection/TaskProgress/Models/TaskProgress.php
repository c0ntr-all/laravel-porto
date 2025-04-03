<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskProgress\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Containers\TaskManagerSection\Task\Models
 *
 * @property int $id
 * @property int $user_id
 * @property int $task_id
 * @property string $content
 * @property bool $is_final
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class TaskProgress extends Model
{
    use HasUser;

    protected $table = 'tm_task_progress';

    protected $fillable = [
        'id',
        'user_id',
        'task_id',
        'content',
        'is_final',
        'created_at',
        'updated_at'
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
