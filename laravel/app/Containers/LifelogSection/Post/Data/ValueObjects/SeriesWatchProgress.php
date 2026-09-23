<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\ValueObjects;

use App\Ship\Parents\ValueObjects\ValueObject;
use InvalidArgumentException;

final class SeriesWatchProgress extends ValueObject
{
    public function __construct(
        public readonly int $season,
        public readonly int $episodeFrom,
        public readonly int $episodeTo,
        public readonly ?string $stoppedAt = null,
    ) {
        if ($this->season < 1) {
            throw new InvalidArgumentException('season must be >= 1.');
        }

        if ($this->episodeFrom < 1) {
            throw new InvalidArgumentException('episode_from must be >= 1.');
        }

        if ($this->episodeTo < $this->episodeFrom) {
            throw new InvalidArgumentException('episode_to must be >= episode_from.');
        }

        if ($this->stoppedAt !== null && !$this->isValidTime($this->stoppedAt)) {
            throw new InvalidArgumentException('stopped_at must be H:i or H:i:s.');
        }
    }

    public static function fromArray(array $data): static
    {
        $stoppedAt = $data['stopped_at'] ?? null;
        if ($stoppedAt === '') {
            $stoppedAt = null;
        }

        return new self(
            season: (int) ($data['season'] ?? 0),
            episodeFrom: (int) ($data['episode_from'] ?? 0),
            episodeTo: (int) ($data['episode_to'] ?? 0),
            stoppedAt: is_string($stoppedAt) ? self::normalizeTime($stoppedAt) : null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'season' => $this->season,
            'episode_from' => $this->episodeFrom,
            'episode_to' => $this->episodeTo,
            'stopped_at' => $this->stoppedAt,
        ];

        return array_filter(
            $data,
            static fn (mixed $value): bool => $value !== null
        );
    }

    private static function normalizeTime(string $time): string
    {
        $time = trim($time);

        if (preg_match('/^\d{1,2}:\d{2}$/', $time) === 1) {
            [$h, $m] = array_map('intval', explode(':', $time));

            return sprintf('%02d:%02d:00', $h, $m);
        }

        if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $time) === 1) {
            [$h, $m, $s] = array_map('intval', explode(':', $time));

            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        }

        throw new InvalidArgumentException('stopped_at must be H:i or H:i:s.');
    }

    private function isValidTime(string $time): bool
    {
        return preg_match('/^\d{2}:\d{2}:\d{2}$/', $time) === 1;
    }
}
