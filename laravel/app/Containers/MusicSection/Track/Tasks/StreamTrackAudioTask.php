<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class StreamTrackAudioTask extends ParentTask
{
    public function run(string $absolutePath, string $downloadName): BinaryFileResponse
    {
        $response = new BinaryFileResponse(
            $absolutePath,
            200,
            [
                'Content-Type' => $this->mimeType($absolutePath),
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'private, no-transform, max-age=0, must-revalidate',
                'X-Content-Type-Options' => 'nosniff',
                'X-Accel-Buffering' => 'no',
            ],
            false,
        );

        $asciiFallback = preg_replace('/[^\x20-\x7E]/', '_', $downloadName) ?: 'track';
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $downloadName,
            $asciiFallback,
        );

        return $response;
    }

    private function mimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'mp3' => 'audio/mpeg',
            'm4a', 'mp4' => 'audio/mp4',
            'flac' => 'audio/flac',
            'ogg', 'oga' => 'audio/ogg',
            'wav' => 'audio/wav',
            'aac' => 'audio/aac',
            default => mime_content_type($path) ?: 'application/octet-stream',
        };
    }
}
