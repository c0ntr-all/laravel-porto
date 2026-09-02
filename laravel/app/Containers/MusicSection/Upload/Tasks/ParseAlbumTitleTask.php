<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Album\Models\AlbumType;
use App\Containers\MusicSection\Upload\Support\DiscMarkerParser;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ParseAlbumTitleTask extends ParentTask
{
    /**
     * Parentheticals that are credits or a year, not editions or discs.
     */
    private const string NOISE_PATTERN = '/^(?:feat|ft|featuring)\b|^\d{4}$/i';

    public function __construct(
        private readonly DiscMarkerParser $discMarkerParser,
    ) {
    }

    /**
     * @param Collection<int, object>|iterable<int, object> $albumTypes
     * @return array{
     *     name: string,
     *     edition: string|null,
     *     original_album: string|null,
     *     album_type_id: int,
     *     disc_number: int|null
     * }
     */
    public function run(string $albumTitle, iterable $albumTypes): array
    {
        $albumTypes = Collection::make($albumTypes);
        $name = trim($albumTitle);
        $editionParts = [];
        $albumTypeId = 1;
        $discNumber = null;

        if ($name !== '' && preg_match_all('/\(([^)]+)\)|\[([^\[\]]+)\]/', $name, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $match) {
                $attribute = trim(($match[1] ?? '') !== '' ? $match[1] : ($match[2] ?? ''));
                if ($attribute === '') {
                    continue;
                }

                $lower = mb_strtolower($attribute);
                $matchedType = $this->matchAlbumType($albumTypes, $lower);

                if ($matchedType !== null) {
                    $albumTypeId = (int) $matchedType->id;
                    $name = str_replace($match[0], '', $name);
                    continue;
                }

                $fromDisc = $this->discMarkerParser->matchInner($attribute);
                if ($fromDisc !== null) {
                    $discNumber = $fromDisc;
                    $name = str_replace($match[0], '', $name);
                    continue;
                }

                if ($this->isNoise($lower)) {
                    continue;
                }

                $editionParts[] = $attribute;
                $name = str_replace($match[0], '', $name);
            }
        }

        $trailing = $this->discMarkerParser->stripTrailing($name);
        $name = $trailing['name'];
        if ($trailing['disc_number'] !== null) {
            $discNumber = $trailing['disc_number'];
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
            'disc_number' => $discNumber,
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
}
