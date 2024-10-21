<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class PrepareUploadMusicTreeTask extends ParentTask
{
    private string $path;

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
        'digipack'
    ];

    public function __construct(
        private readonly ParseTracksFromFolderTask $parseTracksFromFolderTask,
        private readonly GetTracksInfoTask $getTracksInfoTask,
        private readonly GetAlbumCoverTask $getAlbumCoverTask
    )
    {
    }

    /**
     * @throws Exception
     */
    public function run(string $path): array
    {
        $this->path = $path;

        $allTracksPaths = $this->parseTracksFromFolderTask->run($path);
        $allTracksInfo = $this->getTracksInfoTask->run($allTracksPaths);

        $library = $this->makeLibraryTree($allTracksInfo);

        return $this->formatLibraryTree($library);

    }

    /**
     * An intermediate version of the tree for convenient further data processing
     *
     * @param array $tracks
     * @return array
     */
    private function makeLibraryTree(array $tracks): array
    {
        $library = [];

        foreach ($tracks as $track) {
            $artist = $this->getArtistFromTrack($track);
            $album = $this->getAlbumFromTrack($track);

            $artistName = $artist['name'];
            $albumName = $this->addYearToAlbumName($track);

            if (!isset($library[$artistName])) {
                $library[$artistName] = $artist;
            }

            if (!isset($library[$artistName]['albums'][$albumName])) {
                $library[$artistName]['albums'][$albumName] = $album;
            }

            $library[$artistName]['albums'][$albumName]['tracks'][] = $track;
        }

        return $library;
    }

    /**
     * Data processing and further tree formatting like Artists -> Albums -> Tracks
     *
     * @throws Exception
     */
    private function formatLibraryTree(array $library): array
    {
        $albumTypes = Cache::get('album_types');

        $result = [];

        foreach ($library as $artist) {
            $artistData = [
                'name' => $artist['name'],
                'path' => $artist['path'],
                'image' => null,
                'albums' => []
            ];

            foreach ($artist['albums'] as &$album) {
                $albumAttributes = $this->parseAlbumCircleBraces($album['name']);

                if (!empty($albumAttributes)) {
                    foreach ($albumAttributes[1] as $attribute) {
                        $lowerAttr = strtolower($attribute);
                        $albumType = $albumTypes->firstWhere(fn ($type) => $type->slug === $lowerAttr);

                        // If it's not album type then it's version or edition
                        if ($albumType) {
                            $album['album_type_id'] = $albumType->id;
                        } else {
                            $album['original_album'] = $this->cleanAlbumName($album['name'], $attribute);
                            if ($this->checkIsItVersionString($attribute)) {
                                $album['attributes'] = $attribute;
                            }
                        }
                    }
                }
                $artistData['albums'][] = $album;
                $artistData['image'] = $album['image'];
            }
            $result[] = $artistData;
        }

        return $result;
    }

    /**
     * Preparing data for Artist
     *
     * @param array $track
     * @return array
     */
    private function getArtistFromTrack(array $track): array
    {
        return [
            'name' => $track['artist'],
            'path' => $this->path,
            'image' => '',
        ];
    }

    /**
     * Preparing data for Album
     *
     * @param array $track
     * @return array
     */
    private function getAlbumFromTrack(array $track): array
    {
        $albumPath = dirname($track['path']);

        return [
            'name' => $track['album'],
            'cd' => $track['cd'] ?? 1,
            'album_type_id' => 1,
            'image' => $this->getAlbumCoverTask->run($albumPath),
            'date' => $track['date'],
            'is_date_verified' => false,
            'path' => $albumPath
        ];
    }

    /**
     * Get data from circle braces in Album
     *
     * @param string $albumName
     * @return array|null
     */
    public function parseAlbumCircleBraces(string $albumName): ?array
    {
        if (preg_match_all('/\((.*?)\)/', $albumName, $matches)) {
            return $matches;
        }

        return NULL;
    }

    public function cleanAlbumName(string $albumName, string $crap): string
    {
        return trim(str_replace("($crap)", '', $albumName));
    }

    /**
     * Make the album name unique by using the year because remasters can have the same name
     *
     * @param array $track
     * @return string
     */
    private function addYearToAlbumName(array $track): string
    {
        return $track['album'] . '_' . Carbon::parse($track['date'])->format('Y');
    }

    /**
     * @param string $string
     * @return bool
     */
    private function checkIsItVersionString(string $string): bool
    {
        foreach (static::VERSION_KEYWORDS as $keyword) {
            if (str_contains($string, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
