<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Support;

/**
 * Extracts performing collaborators (feat/ft/vs) from credit fragments or artist tags.
 * Producers and mix credits are ignored — those stay as text on the track.
 */
final class FeaturedArtistParser
{
    public const PERFORMER_LEAD_IN = '(?:feat(?:uring)?|ft|vs\.?|versus)';

    /**
     * @return list<string>
     */
    public function namesFromCredits(?string $credits): array
    {
        if ($credits === null || trim($credits) === '') {
            return [];
        }

        $names = [];
        foreach (preg_split('/\s+\/\s+/', $credits) ?: [] as $fragment) {
            foreach ($this->namesFromFragment(trim($fragment)) as $name) {
                $names[] = $name;
            }
        }

        return $this->unique($names);
    }

    /**
     * @return list<string>
     */
    public function namesFromFragment(string $fragment): array
    {
        $fragment = trim($fragment);
        if ($fragment === '') {
            return [];
        }

        if (preg_match('/^'.self::PERFORMER_LEAD_IN.'\b[\s.:\-]*(.+)$/iu', $fragment, $matches) !== 1) {
            return [];
        }

        return $this->splitNames($matches[1]);
    }

    /**
     * @return array{name: string, featured_artists: list<string>}
     */
    public function parseArtistField(string $artist): array
    {
        $name = trim($artist);
        if ($name === '') {
            return ['name' => '', 'featured_artists' => []];
        }

        if (preg_match('/^(.+?)\s+('.self::PERFORMER_LEAD_IN.')\b[\s.:\-]*(.+)$/iu', $name, $matches) !== 1) {
            return ['name' => $name, 'featured_artists' => []];
        }

        $primary = trim($matches[1]);
        $featured = $this->splitNames($matches[3]);

        if ($primary === '') {
            return ['name' => $name, 'featured_artists' => []];
        }

        return [
            'name' => $primary,
            'featured_artists' => $this->unique(array_values(array_filter(
                $featured,
                static fn (string $guest): bool => strcasecmp($guest, $primary) !== 0,
            ))),
        ];
    }

    /**
     * @return list<string>
     */
    public function splitNames(string $raw): array
    {
        $raw = trim($raw, " \t.-");
        if ($raw === '') {
            return [];
        }

        $parts = preg_split(
            '/\s*(?:&|\+|\;|\sx\s+|,(?!\s+(?:the|a|an|jr\.?|sr\.?|ii|iii|iv)\b))\s*/iu',
            $raw,
        );

        $names = [];
        foreach ($parts ?: [] as $part) {
            $name = trim($part, " \t.-");
            if ($name !== '') {
                $names[] = $name;
            }
        }

        return $this->unique($names);
    }

    /**
     * @param list<string> $names
     * @return list<string>
     */
    private function unique(array $names): array
    {
        $seen = [];
        $unique = [];

        foreach ($names as $name) {
            $key = mb_strtolower($name);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $name;
        }

        return $unique;
    }
}
