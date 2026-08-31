<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ParseAlbumTitleTask extends ParentTask
{
    /**
     * Parentheticals that are disc/track markers or credits, not editions.
     */
    private const string NOISE_PATTERN = '/^(?:cd|disc|disk|dvd|sacd|part|vol|volume)\s*\.?\s*\d*$|^(?:feat|ft|featuring)\b|^\d{4}$/i';

    /**
     * @param Collection<int, object>|iterable<int, object> $albumTypes
     * @return array{
     *     name: string,
     *     edition: string|null,
     *     original_album: string|null,
     *     album_type_id: int
     * }
     */
    public function run(string $albumTitle, iterable $albumTypes): array
    {
        $albumTypes = Collection::make($albumTypes);
        $name = trim($albumTitle);
        $editionParts = [];
        $albumTypeId = 1;

        if ($name === '' || !preg_match_all('/\(([^)]+)\)/', $name, $matches)) {
            return [
                'name' => $name,
                'edition' => null,
                'original_album' => null,
                'album_type_id' => $albumTypeId,
            ];
        }

        foreach ($matches[1] as $attribute) {
            $trimmed = trim($attribute);
            if ($trimmed === '') {
                continue;
            }

            $lower = mb_strtolower($trimmed);
            $matchedType = $this->matchAlbumType($albumTypes, $lower);

            if ($matchedType !== null) {
                $albumTypeId = (int) $matchedType->id;
                $name = $this->stripParenthetical($name, $attribute);
                continue;
            }

            if ($this->isNoise($lower)) {
                continue;
            }

            $editionParts[] = $trimmed;
            $name = $this->stripParenthetical($name, $attribute);
        }

        $cleaned = trim(preg_replace('/\s+/', ' ', $name) ?? '');
        if ($cleaned === '') {
            $cleaned = trim($albumTitle);
        }

        $edition = $editionParts === [] ? null : implode(' / ', array_values(array_unique($editionParts)));

        return [
            'name' => $cleaned,
            'edition' => $edition,
            'original_album' => $edition !== null ? $cleaned : null,
            'album_type_id' => $albumTypeId,
        ];
    }

    private function matchAlbumType(Collection $albumTypes, string $lower): ?object
    {
        return $albumTypes->first(function (object $type) use ($lower): bool {
            $slug = mb_strtolower((string) $type->slug);

            return $slug === $lower
                || mb_strtolower((string) $type->name) === $lower
                || Str::slug($lower) === $slug;
        });
    }

    private function isNoise(string $lower): bool
    {
        return (bool) preg_match(self::NOISE_PATTERN, $lower);
    }

    private function stripParenthetical(string $title, string $attribute): string
    {
        return str_replace('('.$attribute.')', '', $title);
    }
}
