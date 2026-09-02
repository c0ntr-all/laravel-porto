<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Support;

/**
 * Detects disc/CD markers in album titles so they can be stored as discs, not as part of the name.
 *
 * Matches: (CD1), [Disc 2], CD 1, Disc.2, DVD 3 of 4, SACD-1
 */
final class DiscMarkerParser
{
    public const TOKEN = '(?:cd|disc|disk|dvd|sacd)';

    /** Inner text of () or [] that is only a disc marker. */
    public const INNER_PATTERN = '/^'.self::TOKEN.'\s*\.?\s*[-:]?\s*(\d+)(?:\s*(?:of|\/)\s*\d+)?$/i';

    /** Disc marker glued to the end of a title: "Album CD1", "Album - Disc 2". */
    public const TRAILING_PATTERN = '/(?:^|[\s\-–—\/|,])'.self::TOKEN.'\s*\.?\s*[-:]?\s*(\d+)(?:\s*(?:of|\/)\s*\d+)?\s*$/i';

    public function matchInner(string $value): ?int
    {
        if (preg_match(self::INNER_PATTERN, trim($value), $matches) !== 1) {
            return null;
        }

        $number = (int) $matches[1];

        return $number > 0 ? $number : null;
    }

    /**
     * @return array{name: string, disc_number: int|null}
     */
    public function stripTrailing(string $title): array
    {
        $name = trim($title);
        $discNumber = null;

        if ($name !== '' && preg_match(self::TRAILING_PATTERN, $name, $matches) === 1) {
            $discNumber = (int) $matches[1];
            $name = trim(preg_replace(self::TRAILING_PATTERN, '', $name) ?? $name);
        }

        return [
            'name' => $name,
            'disc_number' => $discNumber > 0 ? $discNumber : null,
        ];
    }
}
