<?php declare(strict_types=1);

namespace App\Ship\Tasks;

use App\Ship\Parents\Tasks\Task as ParentTask;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class StreamLocalFileTask extends ParentTask
{
    public function run(string $absolutePath, string $downloadName, ?string $mime = null): BinaryFileResponse
    {
        $response = new BinaryFileResponse(
            $absolutePath,
            200,
            [
                'Content-Type' => $mime ?: $this->mimeType($absolutePath),
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'private, no-transform, max-age=0, must-revalidate',
                'X-Content-Type-Options' => 'nosniff',
                'X-Accel-Buffering' => 'no',
            ],
            false,
        );

        $asciiFallback = preg_replace('/[^\x20-\x7E]/', '_', $downloadName) ?: 'file';
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
            'jpg', 'jpeg', 'jfif' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'mp4', 'm4v' => 'video/mp4',
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
            'mkv' => 'video/x-matroska',
            'avi' => 'video/x-msvideo',
            '3gp' => 'video/3gpp',
            default => mime_content_type($path) ?: 'application/octet-stream',
        };
    }
}
