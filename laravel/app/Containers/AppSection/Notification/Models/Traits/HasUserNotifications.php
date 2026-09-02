<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Models\Traits;

use App\Containers\AppSection\Notification\Models\UserNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasUserNotifications
{
    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }
}
