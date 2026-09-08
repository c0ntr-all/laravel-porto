<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityUseCaseLog extends Model
{
    use HasUser;

    protected $fillable = [
        'user_id',
        'correlation_uuid',
        'loggable_type',
        'loggable_id',
        'event_type',
    ];

    const null UPDATED_AT = null;

    public function systemLogs(): HasMany
    {
        return $this->hasMany(ActivitySystemLog::class, 'correlation_uuid', 'correlation_uuid')
            ->orderBy('created_at')
            ->orderBy('id');
    }
}
