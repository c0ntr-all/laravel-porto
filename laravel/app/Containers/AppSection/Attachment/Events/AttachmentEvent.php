<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Events;

use App\Containers\AppSection\Attachment\Models\Attachment;
use App\Ship\Events\DomainActivityEvent;
use Illuminate\Database\Eloquent\Model;

abstract class AttachmentEvent extends DomainActivityEvent
{
    public function __construct(
        protected Attachment $attachment
    ) {
    }

    public function getAttachment(): Attachment
    {
        return $this->attachment;
    }

    public function activityMainType(): string
    {
        return (string) $this->attachment->attachable_type;
    }

    public function activityMainId(): string
    {
        return (string) $this->attachment->attachable_id;
    }

    public function activityRelatedType(): ?string
    {
        return (string) $this->attachment->fileable_type;
    }

    public function activityRelatedId(): ?string
    {
        return (string) $this->attachment->fileable_id;
    }

    public function activityMetadata(): array
    {
        return [
            'attachment_id' => $this->attachment->id,
            'fileable_type' => $this->attachment->fileable_type,
            'fileable_id' => $this->attachment->fileable_id,
        ];
    }

    protected function activitySubject(): Model
    {
        return $this->attachment;
    }
}
