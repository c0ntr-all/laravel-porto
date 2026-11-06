<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Containers\TaskManagerSection\Task\Models
 *
 * @property int $id
 * @property int $user_id
 * @property int $task_id
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class Checklist extends ActivityLoggableModel
{
    use HasUser;

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::TM_CHECKLIST;

    protected $table = 'tm_checklists';

    protected $fillable = [
        'user_id',
        'task_id',
        'title',
        'created_at',
        'updated_at'
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class, 'checklist_id', 'id');
    }
}
