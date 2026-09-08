<?php declare(strict_types=1);

namespace App\Ship\Events;

use App\Ship\Contracts\RecordsActivity;
use App\Ship\Enums\EventTypesEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

abstract class DomainActivityEvent implements RecordsActivity
{
    use SerializesModels;

    protected string $eventType = 'unknown';

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function activityEventType(): string
    {
        return $this->eventType;
    }

    public function activityUserId(): ?int
    {
        $userId = $this->activitySubjectUserId();

        if ($userId !== null) {
            return (int) $userId;
        }

        $authId = auth()->id();

        return $authId !== null ? (int) $authId : null;
    }

    public function activityRelatedType(): ?string
    {
        return null;
    }

    public function activityRelatedId(): ?string
    {
        return null;
    }

    protected function activitySubjectUserId(): int|string|null
    {
        $subject = $this->activitySubject();

        return $subject->getAttribute('user_id');
    }

    protected function snapshot(array $attributes): array
    {
        $subject = $this->activitySubject();

        if ($this->eventType === EventTypesEnum::UPDATED->value) {
            $changes = $subject->getChanges();

            return $changes !== [] ? $changes : $subject->only($attributes);
        }

        return $subject->only($attributes);
    }

    abstract protected function activitySubject(): Model;
}
