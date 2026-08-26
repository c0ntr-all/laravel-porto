<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Upload\Data\DTO\ParsedTrackDto;
use App\Containers\MusicSection\Upload\Helpers\PathHelper;
use App\Containers\MusicSection\Upload\Support\Id3Reader;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Throwable;

class ExtractId3TagsTask extends ParentTask
{
    public function __construct(
        private readonly Id3Reader $id3Reader,
        private readonly FindAlbumCoverTask $findAlbumCoverTask,
    ) {
    }

    public function run(string $linuxPath, string $fallbackArtist): ParsedTrackDto
    {
        try {
            $info = $this->id3Reader->read($linuxPath);
        } catch (Throwable $exception) {
            throw new \RuntimeException('Failed to read ID3 tags from ' . $linuxPath . ': ' . $exception->getMessage(), 0, $exception);
        }

        $comments = $info['comments'] ?? [];
        $albumLinuxPath = dirname($linuxPath);
        $title = $this->first($comments['title'] ?? []) ?? pathinfo($linuxPath, PATHINFO_FILENAME);
        $album = $this->first($comments['album'] ?? []) ?? basename($albumLinuxPath);
        $year = $this->extractYear($comments, $albumLinuxPath);

        return ParsedTrackDto::from([
            'linux_path' => $linuxPath,
            'windows_path' => PathHelper::toWindows($linuxPath),
            'title' => $title,
            'album' => $album,
            'artist' => $this->first($comments['artist'] ?? [])
                ?? $this->first($comments['albumartist'] ?? [])
                ?? $this->first($comments['band'] ?? [])
                ?? $fallbackArtist,
            'genre' => $this->first($comments['genre'] ?? []),
            'year' => $year,
            'date' => $year ? $year . '-01-01' : null,
            'track_number' => $this->extractNumber($this->first($comments['track_number'] ?? $comments['track'] ?? [])),
            'disc_number' => max(1, $this->extractNumber($this->first($comments['partofaset'] ?? $comments['discnumber'] ?? $comments['disc_number'] ?? []) ?: '1')),
            'duration' => $this->formatDuration($info['playtime_seconds'] ?? $info['playtime_string'] ?? null),
            'bitrate' => $this->formatBitrate($info['audio']['bitrate'] ?? null),
            'album_cover_linux_path' => $this->findAlbumCoverTask->run($albumLinuxPath),
            'album_windows_path' => PathHelper::toWindows($albumLinuxPath),
            'album_version' => null,
            'original_album' => null,
            'album_type_id' => 1,
        ]);
    }

    private function first(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function extractNumber(?string $value): int
    {
        if ($value === null) {
            return 0;
        }

        if (preg_match('/^(\d+)/', $value, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    private function extractYear(array $comments, string $albumLinuxPath): ?string
    {
        $candidates = [
            $this->first($comments['year'] ?? []),
            $this->first($comments['date'] ?? []),
            $this->first($comments['recording_time'] ?? []),
            basename($albumLinuxPath),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate && preg_match('/\b(19|20)\d{2}\b/', $candidate, $matches)) {
                return $matches[0];
            }
        }

        return null;
    }

    private function formatDuration(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $seconds = (int) round((float) $value);
        } else {
            $parts = array_map('intval', explode(':', (string) $value));
            if (count($parts) === 2) {
                $seconds = $parts[0] * 60 + $parts[1];
            } elseif (count($parts) === 3) {
                $seconds = $parts[0] * 3600 + $parts[1] * 60 + $parts[2];
            } else {
                return null;
            }
        }

        return sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    }

    private function formatBitrate(mixed $value): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        $bitrate = (int) round((float) $value);

        if ($bitrate > 10000) {
            return (int) round($bitrate / 1000);
        }

        return $bitrate;
    }
}
