<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Models;

use App\Containers\AppSection\Comment\Models\Traits\HasComments;
use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\TaskManagerSection\Checklist\Models\Checklist;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Containers\TaskManagerSection\TaskProgress\Models\TaskProgress;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Tasks\Task
 *
 * @property int $id
 * @property int $task_list_id
 * @property string $title
 * @property string|null $content
 * @property bool $is_declined
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task query()
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Task extends ActivityLoggableModel
{
    use HasUser,
        HasComments;

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::TM_TASK;

    protected $table = 'tm_tasks';

    protected $fillable = [
        'id',
        'user_id',
        'task_list_id',
        'title',
        'content',
        'finished_at',
        'is_declined',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'finished_at' => 'datetime',
        'is_declined' => 'bool',
    ];

    public function taskList(): BelongsTo
    {
        return $this->belongsTo(TaskList::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(TaskProgress::class);
    }

    public function reminder(): HasOne
    {
        return $this->hasOne(Reminder::class);
    }
}
