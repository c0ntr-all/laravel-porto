<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\TaskManagerSection\Task\Models\Task;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use App\Ship\Models\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @mixin \Eloquent
 */
class TaskList extends ActivityLoggableModel
{
    use HasUser,
        HasUuidV7,
        SoftDeletes;

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::TM_TASK_LIST;

    protected $table = 'tm_task_lists';

    protected $fillable = [
        'user_id',
        'title',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_list_id', 'id');
    }
}
