<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Support;

/**
 * Pulls collaboration/production credits out of a track title.
 *
 * Only fragments that start with a known credit lead-in are removed.
 * Artistic parentheticals stay in the title: (Acoustic), (Live), (It Happened One Night).
 */
final class TrackTitleCreditsParser
{
    /**
     * Lead-in of a credit fragment (no delimiters). Keep this list tight so song subtitles survive.
     */
    public const LEAD_IN = '(?:feat(?:uring)?|ft|prod(?:uced)?(?:\s+by)?|prod\.?\s*by|mixed\s+by|remixed\s+by|remix\s+by|vs\.?|versus|scratch(?:es)?\s+by|vocals?\s+by|additional\s+vocals?|rap(?:ped)?\s+by|guest(?:ing)?(?:\s+vocals?)?|courtesy\s+of|explicit|clean(?:\s+version)?)';

    public const INNER_PATTERN = '/^'.self::LEAD_IN.'\b.*$/iu';

    public const BRACKET_PATTERN = '/\[([^\[\]]+)\]|\(([^()]+)\)/u';

    public function __construct(
        private readonly FeaturedArtistParser $featuredArtistParser,
    ) {
    }

    /**
     * @return array{name: string, credits: string|null, featured_artists: list<string>}
     */
    public function parse(string $title): array
    {
        $name = trim($title);
        $credits = [];

        if ($name === '' || preg_match_all(self::BRACKET_PATTERN, $name, $matches, PREG_SET_ORDER) === 0) {
            return [
                'name' => $name,
                'credits' => null,
                'featured_artists' => [],
            ];
        }

        foreach ($matches as $match) {
            $inner = trim(($match[1] ?? '') !== '' ? $match[1] : ($match[2] ?? ''));
            if ($inner === '' || !$this->isCredit($inner)) {
                continue;
            }

            $credits[] = $inner;
            $name = str_replace($match[0], '', $name);
        }

        $cleaned = trim(preg_replace('/\s+/', ' ', $name) ?? '');
        if ($cleaned === '') {
            $cleaned = trim($title);
            $credits = [];
        }

        $credits = array_values(array_unique($credits));
        $joined = $credits === [] ? null : implode(' / ', $credits);

        return [
            'name' => $cleaned,
            'credits' => $joined,
            'featured_artists' => $this->featuredArtistParser->namesFromCredits($joined),
        ];
    }

    public function isCredit(string $inner): bool
    {
        return preg_match(self::INNER_PATTERN, trim($inner)) === 1;
    }
}
