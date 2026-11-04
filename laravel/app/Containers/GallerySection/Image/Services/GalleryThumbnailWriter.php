<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Services;

use Intervention\Image\Laravel\Facades\Image as ImageFacade;
use Symfony\Component\Process\Exception\ExceptionInterface;
use Symfony\Component\Process\Process;

class GalleryThumbnailWriter
{
    /**
     * Writes a small thumbnail without keeping a full bitmap in PHP when Imagick or ffmpeg is available.
     * Imagick is asked to shrink JPEG on load. ffmpeg does the decode in its own process.
     * Intervention is the fallback and does decode the frame.
     */
    public function write(string $source, string $destination, int $maxWidth, int $maxHeight): void
    {
        if (!is_file($source)) {
            throw new \RuntimeException('Source image was not found.');
        }

        $directory = dirname($destination);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create thumbnail directory.');
        }

        if ($this->writeWithImagick($source, $destination, $maxWidth, $maxHeight)) {
            return;
        }

        if ($this->writeWithFfmpeg($source, $destination, $maxWidth, $maxHeight)) {
            return;
        }

        $this->writeWithIntervention($source, $destination, $maxWidth, $maxHeight);
    }

    private function writeWithImagick(string $source, string $destination, int $maxWidth, int $maxHeight): bool
    {
        if (!extension_loaded('imagick') || !class_exists(\Imagick::class)) {
            return false;
        }

        try {
            $image = new \Imagick();
            $image->setOption('jpeg:size', ($maxWidth * 2) . 'x' . ($maxHeight * 2));
            $image->readImage($source);
            $image->autoOrient();
            $image->thumbnailImage($maxWidth, $maxHeight, true);
            $image->setImageCompressionQuality(75);
            $image->writeImage($destination);
            $image->clear();

            return is_file($destination);
        } catch (\Throwable) {
            return false;
        }
    }

    private function writeWithFfmpeg(string $source, string $destination, int $maxWidth, int $maxHeight): bool
    {
        $ffmpeg = $this->ffmpegBinary();
        if ($ffmpeg === null) {
            return false;
        }

        $filter = sprintf(
            'scale=%d:%d:force_original_aspect_ratio=decrease',
            $maxWidth,
            $maxHeight,
        );

        try {
            $process = new Process([
                $ffmpeg,
                '-hide_banner',
                '-loglevel',
                'error',
                '-y',
                '-i',
                $source,
                '-frames:v',
                '1',
                '-vf',
                $filter,
                '-q:v',
                '5',
                $destination,
            ]);
            $process->setTimeout(60);
            $process->run();
        } catch (ExceptionInterface) {
            return false;
        }

        return $process->isSuccessful() && is_file($destination);
    }

    private function writeWithIntervention(string $source, string $destination, int $maxWidth, int $maxHeight): void
    {
        $image = ImageFacade::read($source)->orient();
        if ($image->height() > $image->width()) {
            [$maxWidth, $maxHeight] = [$maxHeight, $maxWidth];
        }

        $image->scale($maxWidth, $maxHeight)->save($destination, 75);
    }

    private function ffmpegBinary(): ?string
    {
        $process = new Process(['sh', '-c', 'command -v ffmpeg']);
        $process->run();
        if (!$process->isSuccessful()) {
            return null;
        }

        $binary = trim($process->getOutput());

        return $binary !== '' ? $binary : null;
    }
}
