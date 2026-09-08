<?php declare(strict_types=1);

namespace App\Containers\AppSection\Document\Events;

use App\Containers\AppSection\Document\Models\Document;
use App\Ship\Events\DomainActivityEvent;
use Illuminate\Database\Eloquent\Model;

abstract class DocumentEvent extends DomainActivityEvent
{
    public function __construct(
        protected Document $document
    ) {
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function activityMainType(): string
    {
        return $this->document->getLoggableType();
    }

    public function activityMainId(): string
    {
        return (string) $this->document->id;
    }

    public function activityMetadata(): array
    {
        return $this->snapshot(['original_name', 'mime_type', 'extension', 'size']);
    }

    protected function activitySubject(): Model
    {
        return $this->document;
    }
}
