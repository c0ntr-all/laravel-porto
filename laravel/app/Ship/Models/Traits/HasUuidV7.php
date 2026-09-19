<?php declare(strict_types=1);

namespace App\Ship\Models\Traits;

use App\Ship\Helpers\UuidV7;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasUuidV7
{
    public static function bootHasUuidV7(): void
    {
        static::creating(static function ($model): void {
            if (blank($model->getAttribute('uuid'))) {
                $model->setAttribute('uuid', UuidV7::generate());
            }
        });
    }

    public function initializeHasUuidV7(): void
    {
        if (!in_array('uuid', $this->fillable, true)) {
            $this->fillable[] = 'uuid';
        }
    }

    public function scopeWhereIdOrUuid(Builder $query, int|string $id): Builder
    {
        $uuidColumn = $query->getModel()->qualifyColumn('uuid');

        return $query->where(function (Builder $inner) use ($id, $uuidColumn): void {
            $inner->whereKey($id);

            if (is_string($id) && Str::isUuid($id)) {
                $inner->orWhere($uuidColumn, $id);
            }
        });
    }
}
