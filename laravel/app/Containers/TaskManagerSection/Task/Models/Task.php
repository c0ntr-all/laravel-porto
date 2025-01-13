<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\Models;

use App\Containers\AppSection\Comment\Models\Traits\HasComments;
use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\TaskManagerSection\TaskList\Models\TaskList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Tasks\Task
 *
 * @property int $id
 * @property int $list_id
 * @property string $title
 * @property string|null $content
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read int|null $comments_count
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
class Task extends Model
{
    use HasUser,
        HasComments;

    protected $fillable = [
        'id',
        'user_id',
        'task_list_id',
        'title',
        'content',
        'finished_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'finished_at' => 'datetime'
    ];

    public function taskList(): BelongsTo
    {
        return $this->belongsTo(TaskList::class);
    }
}
