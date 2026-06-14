<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivitySystemLog extends Model
{
    use HasUser;

    protected $fillable = [
        'user_id',
        'correlation_uuid',
        'event_type',
        'main_type',
        'main_id',
        'related_type',
        'related_id',
        'metadata',
    ];

    const null UPDATED_AT = null;

    protected $casts = [
        'metadata' => 'array',
    ];

    public function userLog(): BelongsTo
    {
        return $this->belongsTo(ActivityUseCaseLog::class, 'correlation_id', 'correlation_id');
    }
}
