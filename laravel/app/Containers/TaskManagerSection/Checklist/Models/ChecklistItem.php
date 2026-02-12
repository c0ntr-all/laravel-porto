<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\TaskManagerSection\Task\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Containers\TaskManagerSection\Checklist\Models
 *
 * @property int $id
 * @property int $user_id
 * @property int $checklist_id
 * @property boolean $is_declined
 * @property string|null $decline_reason
 * @property string $title
 * @property int $position
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class ChecklistItem extends Model
{
    use HasUser;

    protected $table = 'tm_checklist_items';

    protected $fillable = [
        'id',
        'user_id',
        'checklist_id',
        'title',
        'position',
        'is_declined',
        'decline_reason',
        'finished_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'finished_at' => 'datetime',
        'is_declined' => 'bool',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }
}
