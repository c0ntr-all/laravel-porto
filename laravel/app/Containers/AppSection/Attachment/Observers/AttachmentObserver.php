<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Observers;

use App\Containers\AppSection\Attachment\Events\AttachedEvent;
use App\Containers\AppSection\Attachment\Events\DetachedEvent;
use App\Containers\AppSection\Attachment\Models\Attachment;
use Illuminate\Support\Facades\Event;

class AttachmentObserver
{
    public function created(Attachment $attachment): void
    {
        Event::dispatch(new AttachedEvent($attachment));
    }

    public function deleted(Attachment $attachment): void
    {
        Event::dispatch(new DetachedEvent($attachment));
    }
}
