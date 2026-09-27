<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\ValueObjects;

use App\Ship\Parents\ValueObjects\ValueObject;
use InvalidArgumentException;

final class MovieSubjectPayload extends ValueObject
{
    public function __construct(
        public readonly ?SeriesWatchProgress $watch = null,
        public readonly ?string $startedAt = null,
    ) {
        if ($this->startedAt !== null && !$this->isValidStartedAt($this->startedAt)) {
            throw new InvalidArgumentException('started_at must be Y-m-d or Y-m-d H:i.');
        }
    }

    public static function fromArray(array $data): static
    {
        $startedAt = $data['started_at'] ?? null;
        if ($startedAt === '') {
            $startedAt = null;
        }

        $watch = null;
        if (isset($data['season']) || isset($data['episode_from'])) {
            $watch = SeriesWatchProgress::fromArray($data);
        }

        return new self(
            watch: $watch,
            startedAt: is_string($startedAt) ? self::normalizeStartedAt($startedAt) : null,
        );
    }

    public static function make(
        ?SeriesWatchProgress $watch = null,
        ?string $startedAt = null,
    ): ?self {
        if ($watch === null && ($startedAt === null || $startedAt === '')) {
            return null;
        }

        return new self(
            watch: $watch,
            startedAt: $startedAt !== null && $startedAt !== ''
                ? self::normalizeStartedAt($startedAt)
                : null,
        );
    }

    public function isEmpty(): bool
    {
        return $this->watch === null && $this->startedAt === null;
    }

    public function toArray(): array
    {
        $data = $this->watch?->toArray() ?? [];

        if ($this->startedAt !== null) {
            $data['started_at'] = $this->startedAt;
        }

        return $data;
    }

    private static function normalizeStartedAt(string $value): string
    {
        $value = trim($value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return $value;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{1,2}:\d{2}(:\d{2})?$/', $value) === 1) {
            [$date, $time] = explode(' ', $value, 2);
            $parts = array_map('intval', explode(':', $time));
            $hour = $parts[0];
            $minute = $parts[1];

            return sprintf('%s %02d:%02d', $date, $hour, $minute);
        }

        throw new InvalidArgumentException('started_at must be Y-m-d or Y-m-d H:i.');
    }

    private function isValidStartedAt(string $value): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2})?$/', $value) === 1;
    }
}
