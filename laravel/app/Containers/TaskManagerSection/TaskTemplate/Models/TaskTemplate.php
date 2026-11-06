<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use App\Ship\Models\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @mixin \Eloquent
 */
class TaskTemplate extends ActivityLoggableModel
{
    use HasUser,
        HasUuidV7,
        SoftDeletes;

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::TM_TASK_TEMPLATE;

    protected $table = 'tm_task_templates';

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function checklists(): HasMany
    {
        return $this->hasMany(TaskTemplateChecklist::class)
                    ->orderBy('position');
    }
}
