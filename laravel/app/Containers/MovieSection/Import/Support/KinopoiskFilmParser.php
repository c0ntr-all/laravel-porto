<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Support;

use App\Containers\MovieSection\Import\Data\DTO\KinopoiskPageDto;
use App\Containers\MovieSection\Import\Data\DTO\ParsedGenreDto;
use App\Containers\MovieSection\Import\Data\DTO\ParsedKinopoiskFilmDto;
use App\Containers\MovieSection\Import\Exceptions\KinopoiskParseException;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;

class KinopoiskFilmParser
{
    private KinopoiskResponseInspector $inspector;

    public function __construct(?KinopoiskResponseInspector $inspector = null)
    {
        $this->inspector = $inspector ?? new KinopoiskResponseInspector();
    }

    public function parse(KinopoiskPageDto $page): ParsedKinopoiskFilmDto
    {
        $jsonLd = $this->extractJsonLd($page->html);
        $nextData = $this->extractNextDataFilm($page->html, $page->kp_id);
        $meta = $this->extractMeta($page->html);
        $signals = $this->inspector->signals($page->html);

        $sources = [];
        if ($jsonLd !== []) {
            $sources[] = 'json_ld';
        }
        if ($nextData !== []) {
            $sources[] = 'next_data';
        }
        if ($meta !== []) {
            $sources[] = 'html_meta';
        }

        $title = $this->firstString([
            $jsonLd['name'] ?? null,
            $this->nestedString($nextData, ['title', 'russian']),
            $this->nestedString($nextData, ['title', 'original']),
            is_string($nextData['title'] ?? null) ? $nextData['title'] : null,
            $this->nestedString($nextData, ['name', 'russian']),
            is_string($nextData['name'] ?? null) ? $nextData['name'] : null,
            $meta['title'] ?? null,
        ]);

        $year = $this->firstYear([
            $jsonLd['dateCreated'] ?? null,
            $nextData['productionYear'] ?? null,
            $nextData['year'] ?? null,
            $meta['year'] ?? null,
            $title,
        ]);

        $image = $this->normalizeImage($this->firstString([
            $this->imageFromJsonLd($jsonLd),
            $this->nestedString($nextData, ['poster', 'avatarsUrl']),
            $this->nestedString($nextData, ['poster', 'url']),
            $nextData['posterUrl'] ?? null,
            $nextData['coverUrl'] ?? null,
            is_string($nextData['image'] ?? null) ? $nextData['image'] : null,
            $meta['image'] ?? null,
        ]));

        $rating = $this->firstRating([
            $this->nestedString($jsonLd, ['aggregateRating', 'ratingValue']),
            $this->nestedString($nextData, ['rating', 'kinopoisk', 'value']),
            $nextData['ratingKinopoisk'] ?? null,
            $nextData['kinopoiskRating'] ?? null,
            $this->nestedString($nextData, ['rating', 'value']),
            $meta['rating'] ?? null,
        ]);

        $description = $this->firstString([
            $jsonLd['description'] ?? null,
            $this->nestedString($nextData, ['synopsis']),
            $this->nestedString($nextData, ['description']),
            $this->nestedString($nextData, ['shortDescription']),
            $this->nestedString($nextData, ['synopsis', 'value']),
            $this->nestedString($nextData, ['description', 'value']),
            is_string($nextData['synopsis'] ?? null) ? $nextData['synopsis'] : null,
            is_string($nextData['description'] ?? null) ? $nextData['description'] : null,
            is_string($nextData['shortDescription'] ?? null) ? $nextData['shortDescription'] : null,
            $meta['description'] ?? null,
        ]);

        $typeRaw = $this->firstString([
            $this->stringifyType($jsonLd['@type'] ?? null),
            $nextData['__typename'] ?? null,
            $nextData['type'] ?? null,
            $meta['type'] ?? null,
        ]);

        $genres = $this->mergeGenres(
            $this->genresFromJsonLd($jsonLd),
            $this->genresFromNextData($nextData),
        );
        $countries = $this->mergeNames(
            $this->countriesFromJsonLd($jsonLd),
            $this->countriesFromNextData($nextData),
        );

        $extracted = [
            'title' => $title,
            'year' => $year,
            'type' => $typeRaw,
            'kp_img' => $image,
            'kp_rating' => $rating,
            'description' => $description,
            'genres' => array_map(static fn (ParsedGenreDto $genre) => $genre->name, $genres),
            'countries' => $countries,
        ];
        $parserContext = [
            'sources' => $sources,
            'json_ld_keys' => array_keys($jsonLd),
            'next_data_keys' => array_keys($nextData),
            'html_meta' => $meta,
            'signals' => $signals,
        ];

        if ($title === null || $title === '') {
            throw new KinopoiskParseException('Unable to extract film title from Kinopoisk page.', [
                'reason' => 'missing_title',
                'extracted' => $extracted,
                'parser' => $parserContext,
                'kinopoisk' => $this->inspector->snapshot(
                    html: $page->html,
                    requestedUrl: $page->url,
                    finalUrl: $page->final_url,
                    httpStatus: $page->http_status,
                    headers: $page->headers,
                ),
                'attempts' => $page->attempts,
            ]);
        }

        if ($year === null) {
            throw new KinopoiskParseException('Unable to extract film year from Kinopoisk page.', [
                'reason' => 'missing_year',
                'extracted' => $extracted,
                'parser' => $parserContext,
                'kinopoisk' => $this->inspector->snapshot(
                    html: $page->html,
                    requestedUrl: $page->url,
                    finalUrl: $page->final_url,
                    httpStatus: $page->http_status,
                    headers: $page->headers,
                ),
                'attempts' => $page->attempts,
            ]);
        }

        $sourceUrl = $page->final_url ?: $page->url;

        return new ParsedKinopoiskFilmDto(
            kp_id: $page->kp_id,
            title: $title,
            year: $year,
            type: MovieTypeEnum::fromKinopoisk($typeRaw, $sourceUrl),
            cover: $image,
            kp_img: $image,
            kp_rating: $rating,
            description: $description,
            genres: $genres,
            countries: $countries,
            source_url: $sourceUrl,
            parser_sources: $sources,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function extractJsonLd(string $html): array
    {
        if (!preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
            return [];
        }

        foreach ($matches[1] as $json) {
            $data = json_decode(html_entity_decode(trim($json), ENT_QUOTES | ENT_HTML5), true);
            if (!is_array($data)) {
                continue;
            }

            $items = isset($data['@graph']) && is_array($data['@graph']) ? $data['@graph'] : [$data];
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $type = $this->stringifyType($item['@type'] ?? null);
                if ($type !== null && preg_match('/movie|tvseries|tvseason|tvepisode|tvshow/i', $type) === 1) {
                    return $item;
                }
            }
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private function extractNextDataFilm(string $html, int $kpId): array
    {
        if (!preg_match('/<script[^>]*id=["\']__NEXT_DATA__["\'][^>]*>(.*?)<\/script>/is', $html, $match)) {
            return [];
        }

        $data = json_decode(trim($match[1]), true);
        if (!is_array($data)) {
            return [];
        }

        return $this->findFilmNode($data, $kpId) ?? [];
    }

    /**
     * @param array<string, mixed> $node
     * @return array<string, mixed>|null
     */
    private function findFilmNode(array $node, int $kpId): ?array
    {
        $fallback = null;
        $stack = [$node];

        while ($stack !== []) {
            $current = array_pop($stack);
            if (!is_array($current)) {
                continue;
            }

            if ($this->looksLikeFilm($current)) {
                $id = $this->extractNodeId($current);
                if ($id === $kpId) {
                    return $current;
                }
                $fallback ??= $current;
            }

            foreach ($current as $value) {
                if (is_array($value)) {
                    $stack[] = $value;
                }
            }
        }

        return $fallback;
    }

    /**
     * @param array<string, mixed> $node
     */
    private function looksLikeFilm(array $node): bool
    {
        $type = $this->stringifyType($node['__typename'] ?? $node['type'] ?? $node['@type'] ?? null);
        $hasIdentity = isset($node['title']) || isset($node['name']) || isset($node['genres']) || isset($node['productionYear']);

        if ($type !== null && preg_match('/film|movie|tvseries|tvshow|series/i', $type) === 1) {
            return $hasIdentity;
        }

        return isset($node['productionYear']) && (isset($node['genres']) || isset($node['countries']));
    }

    /**
     * @param array<string, mixed> $node
     */
    private function extractNodeId(array $node): ?int
    {
        foreach (['id', 'filmId', 'movieId', 'kinopoiskId', 'kp_id'] as $key) {
            if (isset($node[$key]) && is_numeric($node[$key])) {
                return (int) $node[$key];
            }
        }

        return null;
    }

    /**
     * @return array{title?: string, image?: string, year?: string, rating?: string, type?: string, description?: string}
     */
    private function extractMeta(string $html): array
    {
        $meta = [];
        $meta['title'] = $this->metaContent($html, 'og:title') ?? $this->metaContent($html, 'twitter:title');
        $meta['image'] = $this->metaContent($html, 'og:image') ?? $this->metaContent($html, 'twitter:image');
        $meta['type'] = $this->metaContent($html, 'og:type');
        $meta['year'] = $this->metaContent($html, 'ya:ovs:upload_date') ?? $this->metaContent($html, 'video:release_date');
        $meta['rating'] = $this->metaContent($html, 'ya:ovs:rating');
        $meta['description'] = $this->metaContent($html, 'og:description')
            ?? $this->metaContent($html, 'twitter:description')
            ?? $this->metaContent($html, 'description');

        return array_filter($meta, static fn (mixed $value) => is_string($value) && $value !== '');
    }

    private function metaContent(string $html, string $property): ?string
    {
        $quoted = preg_quote($property, '/');
        $patterns = [
            '/<meta[^>]+property=["\']'.$quoted.'["\'][^>]+content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']'.$quoted.'["\']/i',
            '/<meta[^>]+name=["\']'.$quoted.'["\'][^>]+content=["\']([^"\']+)["\']/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $match) === 1) {
                return html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5);
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $jsonLd
     */
    private function imageFromJsonLd(array $jsonLd): ?string
    {
        $image = $jsonLd['image'] ?? null;
        if (is_string($image)) {
            return $image;
        }

        if (is_array($image)) {
            if (isset($image['url']) && is_string($image['url'])) {
                return $image['url'];
            }
            if (isset($image[0]) && is_string($image[0])) {
                return $image[0];
            }
            if (isset($image[0]['url']) && is_string($image[0]['url'])) {
                return $image[0]['url'];
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $jsonLd
     * @return list<ParsedGenreDto>
     */
    private function genresFromJsonLd(array $jsonLd): array
    {
        $raw = $jsonLd['genre'] ?? [];
        if (is_string($raw)) {
            $raw = [$raw];
        }
        if (!is_array($raw)) {
            return [];
        }

        $genres = [];
        foreach ($raw as $item) {
            $name = is_string($item) ? $item : (is_array($item) ? $this->firstString([$item['name'] ?? null]) : null);
            if ($name === null || $name === '') {
                continue;
            }
            $genres[] = new ParsedGenreDto(name: $this->normalizeName($name));
        }

        return $genres;
    }

    /**
     * @param array<string, mixed> $node
     * @return list<ParsedGenreDto>
     */
    private function genresFromNextData(array $node): array
    {
        $raw = $node['genres']['items'] ?? $node['genres'] ?? [];
        if (!is_array($raw)) {
            return [];
        }

        $genres = [];
        foreach ($raw as $item) {
            if (is_string($item)) {
                $genres[] = new ParsedGenreDto(name: $this->normalizeName($item));
                continue;
            }
            if (!is_array($item)) {
                continue;
            }
            $name = $this->firstString([
                $item['name'] ?? null,
                $item['title'] ?? null,
                $this->nestedString($item, ['name', 'russian']),
            ]);
            if ($name === null) {
                continue;
            }
            $kpId = isset($item['id']) && is_numeric($item['id']) ? (int) $item['id'] : null;
            $genres[] = new ParsedGenreDto(name: $this->normalizeName($name), kp_id: $kpId);
        }

        return $genres;
    }

    /**
     * @param array<string, mixed> $jsonLd
     * @return list<string>
     */
    private function countriesFromJsonLd(array $jsonLd): array
    {
        $raw = $jsonLd['countryOfOrigin'] ?? [];
        if (is_string($raw)) {
            $raw = [$raw];
        }
        if (!is_array($raw)) {
            return [];
        }

        $countries = [];
        foreach ($raw as $item) {
            $name = is_string($item) ? $item : (is_array($item) ? $this->firstString([$item['name'] ?? null]) : null);
            if ($name === null || $name === '') {
                continue;
            }
            $countries[] = $this->normalizeName($name);
        }

        return $countries;
    }

    /**
     * @param array<string, mixed> $node
     * @return list<string>
     */
    private function countriesFromNextData(array $node): array
    {
        $raw = $node['countries']['items'] ?? $node['countries'] ?? [];
        if (!is_array($raw)) {
            return [];
        }

        $countries = [];
        foreach ($raw as $item) {
            $name = is_string($item)
                ? $item
                : (is_array($item) ? $this->firstString([$item['name'] ?? null, $this->nestedString($item, ['name', 'russian'])]) : null);
            if ($name === null || $name === '') {
                continue;
            }
            $countries[] = $this->normalizeName($name);
        }

        return $countries;
    }

    /**
     * @param list<ParsedGenreDto> $primary
     * @param list<ParsedGenreDto> $secondary
     * @return list<ParsedGenreDto>
     */
    private function mergeGenres(array $primary, array $secondary): array
    {
        $merged = [];
        foreach (array_merge($primary, $secondary) as $genre) {
            $key = mb_strtolower($genre->name);
            if (!isset($merged[$key])) {
                $merged[$key] = $genre;
                continue;
            }
            if ($merged[$key]->kp_id === null && $genre->kp_id !== null) {
                $merged[$key] = $genre;
            }
        }

        return array_values($merged);
    }

    /**
     * @param list<string> $primary
     * @param list<string> $secondary
     * @return list<string>
     */
    private function mergeNames(array $primary, array $secondary): array
    {
        $merged = [];
        foreach (array_merge($primary, $secondary) as $name) {
            $key = mb_strtolower($name);
            $merged[$key] ??= $name;
        }

        return array_values($merged);
    }

    /**
     * @param list<mixed> $values
     */
    private function firstString(array $values): ?string
    {
        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }
            $value = trim($value);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param list<mixed> $values
     */
    private function firstYear(array $values): ?int
    {
        foreach ($values as $value) {
            if (is_int($value) && $value >= 1888 && $value <= 2100) {
                return $value;
            }
            if (is_string($value) && preg_match('/(18|19|20)\d{2}/', $value, $match) === 1) {
                return (int) $match[0];
            }
        }

        return null;
    }

    /**
     * @param list<mixed> $values
     */
    private function firstRating(array $values): ?float
    {
        foreach ($values as $value) {
            if (is_numeric($value)) {
                $rating = round((float) $value, 1);
                if ($rating >= 0 && $rating <= 10) {
                    return $rating;
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $path
     */
    private function nestedString(array $data, array $path): ?string
    {
        $current = $data;
        foreach ($path as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        return is_scalar($current) ? trim((string) $current) : null;
    }

    private function stringifyType(mixed $type): ?string
    {
        if (is_string($type)) {
            return $type;
        }
        if (is_array($type)) {
            $parts = array_filter($type, static fn (mixed $item) => is_string($item));

            return $parts === [] ? null : implode(' ', $parts);
        }

        return null;
    }

    private function normalizeImage(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        if (str_contains($url, 'get-kinopoisk-image') && preg_match('/\/(orig|\d+x\d+)$/', $url) !== 1) {
            return rtrim($url, '/').'/orig';
        }

        return $url;
    }

    private function normalizeName(string $name): string
    {
        return trim($name);
    }
}
