<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Models\Traits;

use App\Containers\AppSection\Attachment\Models\Attachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAttachments
{
    protected static function bootHasAttachments(): void
    {
        static::deleting(fn($item) => $item->attachments()->detach());
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
