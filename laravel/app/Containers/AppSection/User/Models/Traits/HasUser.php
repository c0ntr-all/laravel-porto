<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\Models\Traits;

use App\Containers\AppSection\User\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasUser
{
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('user', function ($builder) {
            if (auth()->check()) {
                $builder->where(static::make()->getTable() . '.user_id', '=', auth()->id())
                        ->orWhere(static::make()->getTable() . '.user_id', '=', null);
            }
        });
    }

    public function scopeWhereUser($query, int|string $userId)
    {
        return $query->where($query->getModel()->getTable() . '.user_id', $userId);
    }

    public function scopeWhereUserId($query, int|string $userId)
    {
        return $this->scopeWhereUser($query, $userId);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
