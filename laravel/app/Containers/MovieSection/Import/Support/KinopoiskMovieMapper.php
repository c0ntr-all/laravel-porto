<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Support;

use App\Containers\MovieSection\Import\Data\DTO\ParsedGenreDto;
use App\Containers\MovieSection\Import\Data\DTO\ParsedKinopoiskFilmDto;
use App\Containers\MovieSection\Import\Data\DTO\ParsedPersonDto;
use App\Containers\MovieSection\Import\Exceptions\KinopoiskParseException;
use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use Illuminate\Support\Str;

class KinopoiskMovieMapper
{
    /**
     * @param array<string, mixed> $data
     */
    public function fromApi(array $data, int $kpId, string $sourceUrl = ''): ParsedKinopoiskFilmDto
    {
        $title = $this->firstString([
            $data['name'] ?? null,
            $data['alternativeName'] ?? null,
            $data['enName'] ?? null,
        ]);
        $year = isset($data['year']) && is_numeric($data['year']) ? (int) $data['year'] : null;

        if ($title === null || $title === '') {
            throw new KinopoiskParseException('Unable to extract film title from PoiskKino API.', [
                'reason' => 'missing_title',
            ]);
        }
        if ($year === null || $year < 1888) {
            throw new KinopoiskParseException('Unable to extract film year from PoiskKino API.', [
                'reason' => 'missing_year',
            ]);
        }

        $poster = is_array($data['poster'] ?? null) ? $data['poster'] : [];
        $image = $this->firstString([
            $poster['url'] ?? null,
            $poster['previewUrl'] ?? null,
        ]);
        $rating = is_array($data['rating'] ?? null) ? ($data['rating']['kp'] ?? null) : null;
        $typeRaw = isset($data['type']) ? (string) $data['type'] : null;
        if (($data['isSeries'] ?? false) === true && $typeRaw === null) {
            $typeRaw = 'tv-series';
        }

        return new ParsedKinopoiskFilmDto(
            kp_id: (int) ($data['id'] ?? $kpId),
            title: $title,
            year: $year,
            type: MovieTypeEnum::fromKinopoisk($typeRaw),
            description: $this->firstString([
                $data['description'] ?? null,
            ]),
            short_description: $this->firstString([
                $data['shortDescription'] ?? null,
            ]),
            cover: $image,
            kp_img: $image,
            kp_rating: $this->rating($rating),
            genres: $this->namedList($data['genres'] ?? []),
            countries: $this->namedValues($data['countries'] ?? []),
            persons: $this->persons(is_array($data['persons'] ?? null) ? $data['persons'] : []),
            source_url: $sourceUrl,
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

    private function rating(mixed $value): ?float
    {
        if (!is_numeric($value)) {
            return null;
        }
        $rating = round((float) $value, 1);

        return $rating >= 0 && $rating <= 10 ? $rating : null;
    }

    /**
     * @param list<mixed> $items
     * @return list<ParsedGenreDto>
     */
    private function namedList(array $items): array
    {
        $genres = [];
        foreach ($items as $item) {
            $name = is_string($item) ? $item : (is_array($item) ? $this->firstString([$item['name'] ?? null]) : null);
            if ($name === null) {
                continue;
            }
            $kpId = is_array($item) && isset($item['id']) && is_numeric($item['id']) ? (int) $item['id'] : null;
            $genres[] = new ParsedGenreDto(name: $name, kp_id: $kpId);
        }

        return $genres;
    }

    /**
     * @param list<mixed> $items
     * @return list<string>
     */
    private function namedValues(array $items): array
    {
        $names = [];
        foreach ($items as $item) {
            $name = is_string($item) ? $item : (is_array($item) ? $this->firstString([$item['name'] ?? null]) : null);
            if ($name !== null) {
                $names[] = $name;
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @param list<mixed> $items
     * @return list<ParsedPersonDto>
     */
    private function persons(array $items): array
    {
        $persons = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $kpId = isset($item['id']) && is_numeric($item['id']) ? (int) $item['id'] : null;
            $profession = $this->firstString([$item['profession'] ?? null]);
            $enProfession = $this->firstString([$item['enProfession'] ?? null]);
            if ($enProfession === null && $profession !== null) {
                $enProfession = Str::slug($profession);
            }
            $name = $this->firstString([
                $item['name'] ?? null,
                $item['enName'] ?? null,
            ]);

            if ($kpId === null || $kpId < 1 || $name === null || $enProfession === null || $enProfession === '') {
                continue;
            }

            $persons[] = new ParsedPersonDto(
                kp_id: $kpId,
                name: $name,
                en_profession: $enProfession,
                en_name: $this->firstString([$item['enName'] ?? null]),
                photo: $this->firstString([$item['photo'] ?? null]),
                profession: $profession,
                description: $this->firstString([$item['description'] ?? null]),
            );
        }

        return $persons;
    }
}
