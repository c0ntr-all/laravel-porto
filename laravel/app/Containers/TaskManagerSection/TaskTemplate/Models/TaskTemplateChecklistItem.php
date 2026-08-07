<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $task_template_checklist_id
 * @property string $title
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class TaskTemplateChecklistItem extends Model
{
    use HasUser;

    protected $table = 'tm_task_template_checklist_items';

    protected $fillable = [
        'user_id',
        'task_template_checklist_id',
        'title',
        'position',
        'created_at',
        'updated_at',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(TaskTemplateChecklist::class, 'task_template_checklist_id');
    }
}
