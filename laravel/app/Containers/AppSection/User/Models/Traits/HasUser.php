<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\Models\Traits;

use App\Containers\AppSection\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasUser
{
    /**
     * Rows with a user_id belong to that user. Rows with a null user_id stay
     * hidden, unless the model opts in (system gallery albums).
     */
    protected static function bootHasUser(): void
    {
        static::addGlobalScope('user', function (Builder $builder): void {
            if (!auth()->check()) {
                return;
            }

            $model = $builder->getModel();
            $column = $model->getTable() . '.user_id';

            $builder->where(function (Builder $query) use ($model, $column): void {
                $query->where($column, auth()->id());

                if ($model->sharesUnassignedRows()) {
                    $query->orWhereNull($column);
                }
            });
        });
    }

    public function sharesUnassignedRows(): bool
    {
        return false;
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
