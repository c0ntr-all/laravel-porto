<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Data\DTO;

use App\Ship\Parents\DTO\Data;
use InvalidArgumentException;

class SteamGameAchievementDto extends Data
{
    public function __construct(
        public string $internal_name,
        public string $localized_name,
        public ?string $localized_desc = null,
        public ?string $icon = null,
        public ?string $icon_gray = null,
        public bool $hidden = false,
        public ?string $player_percent_unlocked = null,
        public ?int $internal_key = null,
        public ?int $min_progress_int = null,
        public ?int $max_progress_int = null,
        public ?int $groupid = null,
        public bool $archived = false,
        public ?int $progress_type = null,
    ) {}

    /**
     * @param  array<string, mixed>  $achievement
     */
    public static function fromApi(array $achievement): self
    {
        $internalName = self::stringValue($achievement, 'internal_name');
        if ($internalName === null) {
            throw new InvalidArgumentException('Steam game achievement is missing internal_name.');
        }

        $localizedName = self::stringValue($achievement, 'localized_name') ?? $internalName;

        return new self(
            internal_name: $internalName,
            localized_name: $localizedName,
            localized_desc: self::stringValue($achievement, 'localized_desc'),
            icon: self::stringValue($achievement, 'icon'),
            icon_gray: self::stringValue($achievement, 'icon_gray'),
            hidden: (bool) ($achievement['hidden'] ?? false),
            player_percent_unlocked: self::stringValue($achievement, 'player_percent_unlocked'),
            internal_key: self::intValue($achievement, 'internal_key'),
            min_progress_int: self::intValue($achievement, 'min_progress_int'),
            max_progress_int: self::intValue($achievement, 'max_progress_int'),
            groupid: self::intValue($achievement, 'groupid'),
            archived: (bool) ($achievement['archived'] ?? false),
            progress_type: self::intValue($achievement, 'progress_type'),
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
