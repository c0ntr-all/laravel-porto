<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Models\Traits;

use App\Containers\AppSection\Attachment\Models\Attachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAttachments
{
    protected static function bootHasAttachments(): void
    {
        static::deleting(function ($item): int {
            $attachments = $item->attachments()->get();

            foreach ($attachments as $attachment) {
                $attachment->delete();
            }

            return $attachments->count();
        });
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
