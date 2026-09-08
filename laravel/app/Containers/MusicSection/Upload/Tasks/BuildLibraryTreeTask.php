<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Album\Models\AlbumType;
use App\Containers\MusicSection\Upload\Data\DTO\ParsedTrackDto;
use App\Containers\MusicSection\Upload\Support\FeaturedArtistParser;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Cache;

class BuildLibraryTreeTask extends ParentTask
{
    public function __construct(
        private readonly ParseAlbumTitleTask $parseAlbumTitleTask,
        private readonly ParseTrackTitleTask $parseTrackTitleTask,
        private readonly FeaturedArtistParser $featuredArtistParser,
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
            $track = $this->enrichTrackMeta($track, $albumTypes);
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

        $albums = $this->mergeDiscFolders(array_values($albums));
        $splitTypeId = $this->splitAlbumTypeId($albumTypes);

        foreach ($albums as &$album) {
            $album['artists'] = $this->uniqueArtistNames($album['tracks']);
            $album['discs'] = $this->summarizeDiscs($album['tracks']);

            if (count($album['artists']) > 1 && $splitTypeId !== null) {
                $album['album_type_id'] = $splitTypeId;
            }
        }
        unset($album);

        return [
            'name' => $artistName,
            'path' => $artistWindowsPath,
            'albums' => $albums,
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

    /**
     * @param ParsedTrackDto[] $tracks
     * @return list<array{number: int, name: string, tracks_count: int}>
     */
    private function summarizeDiscs(array $tracks): array
    {
        $counts = [];

        foreach ($tracks as $track) {
            $number = max(1, (int) $track->disc_number);
            $counts[$number] = ($counts[$number] ?? 0) + 1;
        }

        ksort($counts);

        $discs = [];
        foreach ($counts as $number => $tracksCount) {
            $discs[] = [
                'number' => $number,
                'name' => 'CD '.$number,
                'tracks_count' => $tracksCount,
            ];
        }

        return $discs;
    }

    /**
     * Folders like "Album (CD1)" / "Album (CD2)" become one album after the title is cleaned.
     *
     * @param list<array<string, mixed>> $albums
     * @return list<array<string, mixed>>
     */
    private function mergeDiscFolders(array $albums): array
    {
        $buckets = [];
        foreach ($albums as $album) {
            $nameKey = implode("\0", [
                $album['name'],
                $album['date'] ?? '',
                $album['edition'] ?? '',
                (string) $album['album_type_id'],
            ]);
            $buckets[$nameKey][] = $album;
        }

        $merged = [];
        foreach ($buckets as $group) {
            $discNumbers = [];
            foreach ($group as $album) {
                foreach ($album['tracks'] as $track) {
                    $discNumbers[] = max(1, (int) $track->disc_number);
                }
            }

            $shouldMerge = count($group) > 1
                && $discNumbers !== []
                && (max($discNumbers) > 1 || count(array_unique($discNumbers)) > 1);

            if (!$shouldMerge) {
                array_push($merged, ...$group);
                continue;
            }

            $first = $group[0];
            for ($index = 1, $count = count($group); $index < $count; $index++) {
                $first['tracks'] = array_merge($first['tracks'], $group[$index]['tracks']);
            }
            $merged[] = $first;
        }

        return $merged;
    }

    private function splitAlbumTypeId(mixed $albumTypes): ?int
    {
        $split = $albumTypes->first(fn ($type) => strtolower((string) $type->slug) === 'split');

        return $split ? (int) $split->id : null;
    }

    private function enrichTrackMeta(ParsedTrackDto $track, mixed $albumTypes): ParsedTrackDto
    {
        $albumParsed = $this->parseAlbumTitleTask->run($track->album, $albumTypes);
        $titleParsed = $this->parseTrackTitleTask->run($track->title);
        $artistParsed = $this->featuredArtistParser->parseArtistField($track->artist);

        $track->album_type_id = $albumParsed['album_type_id'];
        $track->album_version = $albumParsed['edition'];
        $track->original_album = $albumParsed['original_album'];
        $track->album = $albumParsed['name'];
        $track->title = $titleParsed['name'];
        $track->artist = $artistParsed['name'] !== '' ? $artistParsed['name'] : $track->artist;
        $track->credits = $this->mergeCredits($titleParsed['credits'], $artistParsed['featured_artists']);
        $track->featured_artists = $this->uniqueFeaturedNames(
            $artistParsed['featured_artists'],
            $track->artist,
        );

        if ($track->disc_number === null || $track->disc_number < 1) {
            $track->disc_number = $albumParsed['disc_number'] ?? 1;
        } elseif ($track->disc_number === 1 && $albumParsed['disc_number'] !== null && $albumParsed['disc_number'] !== 1) {
            $track->disc_number = $albumParsed['disc_number'];
        }

        $track->disc_number = max(1, (int) $track->disc_number);

        return $track;
    }

    /**
     * @param list<string> $fromArtistField
     */
    private function mergeCredits(?string $fromTitle, array $fromArtistField): ?string
    {
        $parts = [];
        if ($fromTitle !== null && $fromTitle !== '') {
            $parts[] = $fromTitle;
        }

        foreach ($fromArtistField as $name) {
            $credit = 'feat. '.$name;
            foreach ($parts as $existing) {
                if (stripos($existing, $name) !== false) {
                    continue 2;
                }
            }
            $parts[] = $credit;
        }

        return $parts === [] ? null : implode(' / ', $parts);
    }

    /**
     * @param list<string> $names
     * @return list<string>
     */
    private function uniqueFeaturedNames(array $names, string $primaryArtist): array
    {
        $seen = [];
        $unique = [];

        foreach ($names as $name) {
            $name = trim($name);
            if ($name === '' || strcasecmp($name, $primaryArtist) === 0) {
                continue;
            }

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
