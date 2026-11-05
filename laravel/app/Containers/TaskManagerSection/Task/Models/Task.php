<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Models;

use App\Containers\AppSection\Attachment\Models\Traits\HasAttachments;
use App\Containers\AppSection\Comment\Models\Traits\HasComments;
use App\Containers\AppSection\CustomField\Models\Traits\HasCustomFields;
use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\TaskManagerSection\Checklist\Models\Checklist;
use App\Containers\TaskManagerSection\Checklist\Models\ChecklistItem;
use App\Containers\TaskManagerSection\Reminder\Models\Reminder;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use App\Containers\TaskManagerSection\TaskProgress\Models\TaskProgress;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use App\Ship\Models\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $task_list_id
 * @property int|null $parent_id
 * @property string $title
 * @property string|null $content
 * @property bool $is_declined
 * @property bool $is_favorite
 * @property int $priority
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @mixin \Eloquent
 */
class Task extends ActivityLoggableModel
{
    use HasUser,
        HasComments,
        HasAttachments,
        HasCustomFields,
        HasUuidV7,
        SoftDeletes;

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::TM_TASK;

    protected $table = 'tm_tasks';

    protected $fillable = [
        'user_id',
        'task_list_id',
        'parent_id',
        'title',
        'content',
        'finished_at',
        'is_declined',
        'is_favorite',
        'priority',
    ];

    protected $casts = [
        'finished_at' => 'datetime',
        'is_declined' => 'bool',
        'is_favorite' => 'bool',
        'priority' => 'integer',
    ];

    protected static function booted(): void
    {
        // Use deleted (halt=false): HasAttachments/HasCustomFields deleting callbacks
        // return non-null and stop the until() chain before later deleting listeners.
        static::deleted(function (Task $task): void {
            DB::table('lifelog_post_subjectables')
                ->where('subjectable_type', $task->getMorphClass())
                ->where('subjectable_id', $task->getKey())
                ->delete();
        });
    }

    public function taskList(): BelongsTo
    {
        return $this->belongsTo(TaskList::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
                    ->orderBy('priority')
                    ->orderBy('id');
    }

    public function sourceChecklistItem(): HasOne
    {
        return $this->hasOne(ChecklistItem::class, 'converted_task_id');
    }

    protected function childRouteBindingRelationshipName($childType)
    {
        if ($childType === 'progress') {
            return 'progress';
        }

        return parent::childRouteBindingRelationshipName($childType);
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
