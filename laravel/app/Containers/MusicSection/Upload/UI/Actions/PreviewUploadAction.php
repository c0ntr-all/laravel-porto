<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\Actions;

use App\Containers\MusicSection\Upload\Helpers\PathHelper;
use App\Containers\MusicSection\Upload\Tasks\ParseArtistFolderTask;
use App\Containers\MusicSection\Upload\UI\API\Requests\PreviewRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class PreviewUploadAction extends BaseAction
{
    public function __construct(
        private readonly ParseArtistFolderTask $parseArtistFolderTask,
    ) {
    }

    public function handle(string $windowsPath): array
    {
        $parsed = $this->parseArtistFolderTask->run($windowsPath);
        $tree = $parsed['tree'];

        return [
            'artist' => [
                'name' => $tree['name'],
                'path' => $tree['path'],
                'albums_count' => count($tree['albums']),
                'tracks_count' => $parsed['tracks_found'] - count($parsed['errors']),
            ],
            'albums' => array_map(static function (array $album): array {
                return [
                    'name' => $album['name'],
                    'date' => $album['date'],
                    'path' => $album['path'],
                    'album_type_id' => $album['album_type_id'],
                    'original_album' => $album['original_album'],
                    'tracks_count' => count($album['tracks']),
                    'tracks' => array_map(static function ($track): array {
                        return [
                            'title' => $track->title,
                            'path' => $track->windows_path,
                            'track_number' => $track->track_number,
                            'disc_number' => $track->disc_number,
                            'duration' => $track->duration,
                            'bitrate' => $track->bitrate,
                            'genre' => $track->genre,
                        ];
                    }, $album['tracks']),
                ];
            }, $tree['albums']),
            'errors' => $parsed['errors'],
        ];
    }

    public function asController(PreviewRequest $request): JsonResponse
    {
        $preview = $this->handle(PathHelper::normalizeWindows($request->validated('path')));

        return response()->json([
            'data' => $preview,
            'meta' => [
                'message' => 'Upload preview generated.',
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }
}
