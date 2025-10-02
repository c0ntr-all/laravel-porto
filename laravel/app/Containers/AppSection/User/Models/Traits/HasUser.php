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
            $builder->where(static::make()->getTable() . '.user_id', '=', auth()->user()->id);
        });
    }

    public function scopeWhereUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
