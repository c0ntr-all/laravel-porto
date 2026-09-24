<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Support;

use App\Containers\MovieSection\Import\Data\DTO\ParsedEpisodeDto;
use App\Containers\MovieSection\Import\Data\DTO\ParsedSeasonDto;
use App\Containers\MovieSection\Import\Exceptions\KinopoiskParseException;

class KinopoiskSeasonMapper
{
    /**
     * @param list<array<string, mixed>> $docs
     * @return list<ParsedSeasonDto>
     */
    public function fromDocs(array $docs, int $movieKpId): array
    {
        $seasons = [];

        foreach ($docs as $doc) {
            if (!is_array($doc)) {
                continue;
            }

            $mapped = $this->mapSeason($doc, $movieKpId);
            if ($mapped !== null) {
                $seasons[] = $mapped;
            }
        }

        usort(
            $seasons,
            static fn (ParsedSeasonDto $a, ParsedSeasonDto $b): int => $a->number <=> $b->number,
        );

        return $seasons;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function mapSeason(array $data, int $fallbackMovieKpId): ?ParsedSeasonDto
    {
        $number = $this->intValue($data['number'] ?? null);
        if ($number === null || $number < 0) {
            return null;
        }

        $movieId = $this->intValue($data['movieId'] ?? null) ?? $fallbackMovieKpId;
        if ($movieId < 1) {
            throw new KinopoiskParseException('Unable to extract movieId from PoiskKino season payload.', [
                'reason' => 'missing_movie_id',
            ]);
        }

        $poster = is_array($data['poster'] ?? null) ? $data['poster'] : [];
        $kpId = $this->intValue($data['id'] ?? null);
        $kpSeasonId = $this->intValue($data['seasonId'] ?? null) ?? $kpId;

        $episodes = [];
        if (is_array($data['episodes'] ?? null)) {
            foreach ($data['episodes'] as $episode) {
                if (!is_array($episode)) {
                    continue;
                }

                $mappedEpisode = $this->mapEpisode($episode);
                if ($mappedEpisode !== null) {
                    $episodes[] = $mappedEpisode;
                }
            }

            usort(
                $episodes,
                static fn (ParsedEpisodeDto $a, ParsedEpisodeDto $b): int => $a->number <=> $b->number,
            );
        }

        $episodesCount = $this->intValue($data['episodesCount'] ?? null);
        if ($episodesCount === null) {
            $episodesCount = count($episodes) > 0 ? count($episodes) : null;
        }

        return new ParsedSeasonDto(
            number: $number,
            kp_movie_id: $movieId,
            kp_id: $kpId,
            kp_season_id: $kpSeasonId,
            name: $this->firstString([$data['name'] ?? null]),
            en_name: $this->firstString([$data['enName'] ?? null]),
            air_date: $this->dateValue($data['airDate'] ?? null),
            episodes_count: $episodesCount,
            duration: $this->intValue($data['duration'] ?? null),
            poster: $this->firstString([$poster['url'] ?? null]),
            poster_preview: $this->firstString([$poster['previewUrl'] ?? null, $poster['url'] ?? null]),
            episodes: $episodes,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function mapEpisode(array $data): ?ParsedEpisodeDto
    {
        $number = $this->intValue($data['number'] ?? null);
        if ($number === null || $number < 0) {
            return null;
        }

        $still = is_array($data['still'] ?? null) ? $data['still'] : [];

        return new ParsedEpisodeDto(
            number: $number,
            kp_id: $this->intValue($data['id'] ?? null),
            name: $this->firstString([
                $data['name'] ?? null,
                $data['enName'] ?? null,
            ]),
            description: $this->firstString([$data['description'] ?? null]),
            en_description: $this->firstString([$data['enDescription'] ?? null]),
            duration: $this->intValue($data['duration'] ?? null),
            air_date: $this->dateValue($data['airDate'] ?? $data['date'] ?? null),
            still: $this->firstString([$still['url'] ?? null]),
            still_preview: $this->firstString([$still['previewUrl'] ?? null, $still['url'] ?? null]),
        );
    }

    /**
     * @param list<mixed> $values
     */
    private function firstString(array $values): ?string
    {
        foreach ($values as $value) {
            if (!is_string($value) && !is_numeric($value)) {
                continue;
            }
            $value = trim((string) $value);
            if ($value !== '' && $value !== 'null') {
                return $value;
            }
        }

        return null;
    }

    private function intValue(mixed $value): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function dateValue(mixed $value): ?string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $raw, $matches) === 1) {
            return $matches[0];
        }

        $timestamp = strtotime($raw);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }
}
