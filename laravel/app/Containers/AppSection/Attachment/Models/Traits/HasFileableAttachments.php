<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Models\Traits;

use App\Containers\AppSection\Attachment\Models\Attachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFileableAttachments
{
    public function fileableAttachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'fileable');
    }
}
