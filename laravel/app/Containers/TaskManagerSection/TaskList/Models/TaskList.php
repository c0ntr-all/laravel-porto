<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskList\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Database\Eloquent\Model;
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
class TaskList extends Model
{
    use HasUser,
        SoftDeletes;

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
