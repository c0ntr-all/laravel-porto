<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\Enums;

use App\Ship\Enums\Traits\Arrayable;
use InvalidArgumentException;

enum ReminderTimeUnitEnum: string
{
    use Arrayable;

    case MINUTE = 'minute';
    case HOUR = 'hour';
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';

    /**
     * Units allowed for recurrence interval.
     *
     * @return list<self>
     */
    public static function intervalUnits(): array
    {
        return [
            self::HOUR,
            self::DAY,
            self::WEEK,
            self::MONTH,
            self::YEAR,
        ];
    }

    /**
     * Units allowed for "remind before" offset.
     *
     * @return list<self>
     */
    public static function remindBeforeUnits(): array
    {
        return [
            self::MINUTE,
            self::HOUR,
            self::DAY,
            self::WEEK,
        ];
    }

    /**
     * @return list<string>
     */
    public static function intervalUnitValues(): array
    {
        return array_map(static fn (self $unit) => $unit->value, self::intervalUnits());
    }

    /**
     * @return list<string>
     */
    public static function remindBeforeUnitValues(): array
    {
        return array_map(static fn (self $unit) => $unit->value, self::remindBeforeUnits());
    }

    public function carbonUnit(): string
    {
        return match ($this) {
            self::MINUTE => 'minutes',
            self::HOUR => 'hours',
            self::DAY => 'days',
            self::WEEK => 'weeks',
            self::MONTH => 'months',
            self::YEAR => 'years',
        };
    }

    public function label(): string
    {
        return __('taskManagerSection@reminder::intervals.' . $this->value);
    }

    public static function fromString(string $value): self
    {
        $normalized = strtolower(rtrim(trim($value), 's'));

        return self::tryFrom($normalized)
            ?? throw new InvalidArgumentException("Unsupported reminder time unit [{$value}].");
    }
}
