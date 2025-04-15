<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\AppSection\User\Models\User;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Tasks\TaskList
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Collection<int, \App\Containers\TaskManagerSection\Task\Models\Task> $tasks
 * @property-read int|null $tasks_count
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TaskList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskList query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskList whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskList whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskList whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskList whereUserId($value)
 * @mixin \Eloquent
 */
class TaskList extends Model
{
    use HasUser;

    protected $table = 'tm_task_lists';

    protected $fillable = [
        'id',
        'user_id',
        'title',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_list_id', 'id');
    }
}
