<?php declare(strict_types=1);

namespace App\Ship\Contracts;

interface RecordsActivity
{
    public function activityEventType(): string;

    public function activityUserId(): ?int;

    public function activityMainType(): string;

    public function activityMainId(): string;

    public function activityRelatedType(): ?string;

    public function activityRelatedId(): ?string;

    /** @return array<string, mixed> */
    public function activityMetadata(): array;
}
