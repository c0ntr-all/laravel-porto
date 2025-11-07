<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityUseCaseLog extends Model
{
    use HasUser;

    protected $guarded = [];

    const null UPDATED_AT = null;

    public function systemLogs(): HasMany
    {
        return $this->hasMany(ActivityUseCaseLog::class, 'correlation_id', 'correlation_id');
    }
}
