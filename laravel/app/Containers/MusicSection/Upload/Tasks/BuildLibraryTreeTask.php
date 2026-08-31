<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Album\Models\AlbumType;
use App\Containers\MusicSection\Upload\Data\DTO\ParsedTrackDto;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Cache;

class BuildLibraryTreeTask extends ParentTask
{
    public function __construct(
        private readonly ParseAlbumTitleTask $parseAlbumTitleTask,
    ) {
    }

    /**
     * @param ParsedTrackDto[] $tracks
     */
    public function run(array $tracks, string $artistName, string $artistWindowsPath): array
    {
        $albumTypes = Cache::get('album_types');
        if (!$albumTypes instanceof Enumerable || $albumTypes->isEmpty()) {
            $albumTypes = AlbumType::all();
        }
        $albums = [];

        foreach ($tracks as $track) {
            $track = $this->enrichAlbumMeta($track, $albumTypes);
            $albumKey = $track->album.'_'.($track->year ?? 'unknown').'_'.$track->album_windows_path;

            if (!isset($albums[$albumKey])) {
                $albums[$albumKey] = [
                    'name' => $track->album,
                    'date' => $track->date,
                    'path' => $track->album_windows_path,
                    'album_type_id' => $track->album_type_id,
                    'original_album' => $track->original_album,
                    'edition' => $track->album_version,
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
        $parsed = $this->parseAlbumTitleTask->run($track->album, $albumTypes);

        $track->album_type_id = $parsed['album_type_id'];
        $track->album_version = $parsed['edition'];
        $track->original_album = $parsed['original_album'];
        $track->album = $parsed['name'];

        return $track;
    }
}
