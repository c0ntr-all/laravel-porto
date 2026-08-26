<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Album\Models\AlbumType;
use App\Containers\MusicSection\Upload\Data\DTO\ParsedTrackDto;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\Cache;

class BuildLibraryTreeTask extends ParentTask
{
    private const array VERSION_KEYWORDS = [
        'edition',
        'remastered',
        'japanese',
        'reissue',
        'limited',
        'special',
        'deluxe',
        'expanded',
        'anniversary',
        'digipack',
        'digipak',
    ];

    /**
     * @param ParsedTrackDto[] $tracks
     */
    public function run(array $tracks, string $artistName, string $artistWindowsPath): array
    {
        $albumTypes = Cache::get('album_types') ?? AlbumType::all();
        $albums = [];

        foreach ($tracks as $track) {
            $track = $this->enrichAlbumMeta($track, $albumTypes);
            $albumKey = $track->album . '_' . ($track->year ?? 'unknown') . '_' . $track->album_windows_path;

            if (!isset($albums[$albumKey])) {
                $albums[$albumKey] = [
                    'name' => $track->album,
                    'date' => $track->date,
                    'path' => $track->album_windows_path,
                    'album_type_id' => $track->album_type_id,
                    'original_album' => $track->original_album,
                    'attributes' => $track->album_version,
                    'image' => $track->album_cover_linux_path,
                    'tracks' => [],
                ];
            }

            $albums[$albumKey]['tracks'][] = $track;
        }

        $splitTypeId = $this->splitAlbumTypeId($albumTypes);

        foreach ($albums as &$album) {
            $album['artists'] = $this->uniqueArtistNames($album['tracks']);

            if (count($album['artists']) > 1 && $splitTypeId !== null) {
                $album['album_type_id'] = $splitTypeId;
            }
        }
        unset($album);

        return [
            'name' => $artistName,
            'path' => $artistWindowsPath,
            'albums' => array_values($albums),
        ];
    }

    /**
     * @param ParsedTrackDto[] $tracks
     * @return string[]
     */
    private function uniqueArtistNames(array $tracks): array
    {
        $names = [];

        foreach ($tracks as $track) {
            $name = trim($track->artist);
            if ($name === '') {
                continue;
            }
            $names[$name] = $name;
        }

        return array_values($names);
    }

    private function splitAlbumTypeId(mixed $albumTypes): ?int
    {
        $split = $albumTypes->first(fn ($type) => strtolower((string) $type->slug) === 'split');

        return $split ? (int) $split->id : null;
    }

    private function enrichAlbumMeta(ParsedTrackDto $track, mixed $albumTypes): ParsedTrackDto
    {
        $track->album_type_id = 1;
        $track->original_album = null;
        $track->album_version = null;

        if (!preg_match_all('/\((.*?)\)/', $track->album, $matches)) {
            return $track;
        }

        foreach ($matches[1] as $attribute) {
            $lowerAttr = strtolower($attribute);
            $albumType = $albumTypes->firstWhere(fn ($type) => $type->slug === $lowerAttr);

            if ($albumType) {
                $track->album_type_id = (int) $albumType->id;
                continue;
            }

            $track->original_album = trim(str_replace('(' . $attribute . ')', '', $track->album));
            if ($this->isVersionString($lowerAttr)) {
                $track->album_version = $attribute;
            }
        }

        return $track;
    }

    private function isVersionString(string $string): bool
    {
        foreach (self::VERSION_KEYWORDS as $keyword) {
            if (str_contains($string, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
