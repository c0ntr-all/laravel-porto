<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Data\DTO;

use App\Ship\Parents\DTO\Data;
use InvalidArgumentException;

class SteamPlayerAchievementDto extends Data
{
    public function __construct(
        public string $apiname,
        public bool $achieved,
        public ?int $unlocktime = null,
        public ?string $name = null,
        public ?string $description = null,
    ) {}

    /**
     * @param  array<string, mixed>  $achievement
     */
    public static function fromApi(array $achievement): self
    {
        $apiName = self::stringValue($achievement, 'apiname');
        if ($apiName === null) {
            throw new InvalidArgumentException('Steam player achievement is missing apiname.');
        }

        return new self(
            apiname: $apiName,
            achieved: (int) ($achievement['achieved'] ?? 0) === 1,
            unlocktime: self::intValue($achievement, 'unlocktime'),
            name: self::stringValue($achievement, 'name'),
            description: self::stringValue($achievement, 'description'),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function stringValue(array $data, string $key): ?string
    {
        if (! array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }

        $value = trim((string) $data[$key]);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function intValue(array $data, string $key): ?int
    {
        if (! array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
            return null;
        }

        if (! is_numeric($data[$key])) {
            return null;
        }

        return (int) $data[$key];
    }
}
