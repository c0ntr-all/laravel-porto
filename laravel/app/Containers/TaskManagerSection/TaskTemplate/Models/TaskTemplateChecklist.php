<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $task_template_id
 * @property string $title
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class TaskTemplateChecklist extends Model
{
    use HasUser;

    protected $table = 'tm_task_template_checklists';

    protected $fillable = [
        'user_id',
        'task_template_id',
        'title',
        'position',
        'created_at',
        'updated_at',
    ];

    public function taskTemplate(): BelongsTo
    {
        return $this->belongsTo(TaskTemplate::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TaskTemplateChecklistItem::class, 'task_template_checklist_id')
                    ->orderBy('position');
    }
}
