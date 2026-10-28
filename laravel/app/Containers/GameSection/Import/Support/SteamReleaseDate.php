<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Support;

final class SteamReleaseDate
{
    public static function toDateString(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $raw = trim($raw);
        if ($raw === '' || preg_match('/coming soon|to be announced|\btba\b/i', $raw) === 1) {
            return null;
        }

        foreach (['M j, Y', 'M d, Y', 'j M, Y', 'd M, Y', 'F j, Y', 'j F, Y', 'Y-m-d'] as $format) {
            $date = \DateTimeImmutable::createFromFormat('!'.$format, $raw);
            $errors = \DateTimeImmutable::getLastErrors();
            $clean = $errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0);
            if ($date instanceof \DateTimeImmutable && $clean) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }
}
