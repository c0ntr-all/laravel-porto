<?php declare(strict_types=1);

namespace App\Ship\Helpers;

class DateHelper
{
    public static function secondsToDatetime(float|int|string|null $seconds): string
    {
        if ($seconds === null || $seconds === '') {
            return '00:00:00';
        }

        $seconds = (float) $seconds;

        $integerSeconds = (int) floor($seconds);

        $hours = floor($integerSeconds / 3600);
        $minutes = floor(($integerSeconds % 3600) / 60);
        $secs = $integerSeconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    /**
     * Преобразует строку времени в секунды
     * Поддерживает форматы:
     * - HH:MM:SS (01:23:45)
     * - MM:SS (23:45)
     * - SS (45)
     * - HH:MM:SS.mmm (01:23:45.678)
     *
     * @param string|float|int|null $datetime
     * @return float|null
     */
    public static function datetimeToSeconds($datetime): ?float
    {
        if ($datetime === null || $datetime === '') {
            return null;
        }

        if (is_numeric($datetime)) {
            return (float) $datetime;
        }

        $datetime = trim((string) $datetime);

        $milliseconds = 0;
        if (str_contains($datetime, '.')) {
            $parts = explode('.', $datetime);
            $datetime = $parts[0];
            $milliseconds = (float) ('0.' . $parts[1]);
        }

        $parts = explode(':', $datetime);
        $count = count($parts);

        $seconds = 0;

        switch ($count) {
            case 1: // Только секунды
                $seconds = (int) $parts[0];
                break;

            case 2: // Минуты:Секунды
                $seconds = (int) $parts[0] * 60 + (int) $parts[1];
                break;

            case 3: // Часы:Минуты:Секунды
                $seconds = (int) $parts[0] * 3600 + (int) $parts[1] * 60 + (int) $parts[2];
                break;

            default:
                return null;
        }

        return $seconds + $milliseconds;
    }
}
