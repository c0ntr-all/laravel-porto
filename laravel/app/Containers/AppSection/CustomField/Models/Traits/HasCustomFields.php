<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Models\Traits;

use App\Containers\AppSection\CustomField\Models\CustomField;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCustomFields
{
    protected static function bootHasCustomFields(): void
    {
        static::deleting(fn ($item) => $item->customFields()->delete());
    }

    public function customFields(): MorphMany
    {
        return $this->morphMany(CustomField::class, 'fieldable')
            ->orderBy('position')
            ->orderBy('id');
    }
}
