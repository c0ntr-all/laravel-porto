<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Strategies;

use App\Containers\GallerySection\Image\Contracts\ImageSourceContract;
use App\Containers\GallerySection\Image\Enums\ImageMimeEnum;
use App\Ship\Helpers\StringHelper;
use Intervention\Gif\Exceptions\NotReadableException;
use Intervention\Image\Laravel\Facades\Image as ImageFacade;
use Intervention\Image\Image;
use Random\RandomException;

abstract class AbstractImageSourceStrategy implements ImageSourceContract
{
    private ?Image $image = null;
    private ?string $basename = null;
    private ?string $filename = null;
    private ?string $extension = null;

    public function __construct(
        protected readonly string $path
    ) {
        $pathOnly = $this->pathWithoutQuery($this->path);
        $info = pathinfo($pathOnly);

        $this->filename = ($info['filename'] ?? '') !== '' ? $info['filename'] : null;
        $this->extension = $this->normalizedExtension((string) ($info['extension'] ?? ''));
    }

    public function getFullPath(): string {
        return $this->path;
    }

    public function getOriginalPath(): string {
        return $this->path;
    }

    public function getImage(): Image
    {
        if (!$this->image) {
            $fullPath = $this->getFullPath();

            try {
                $this->image = ImageFacade::read($fullPath)->orient();
            } catch (NotReadableException $e) {
                throw new \RuntimeException("Unable to load image from path: {$fullPath}");
            }
        }

        return clone $this->image;
    }

    /**
     * @throws RandomException
     */
    public function getBasename(): string
    {
        if (!$this->basename) {
            $filename = $this->getFilename();
            $extension = $this->getExtension();

            $this->basename = "{$filename}.{$extension}";
        }

        return $this->basename;
    }

    /**
     * @throws RandomException
     */
    public function getFilename(): string
    {
        if (!$this->filename) {
            $this->filename = StringHelper::generateFilename();
        }

        return $this->filename;
    }

    public function getExtension(): string
    {
        if ($this->extension) {
            return $this->extension;
        }

        $fromPath = $this->normalizedExtension(
            (string) pathinfo($this->pathWithoutQuery($this->path), PATHINFO_EXTENSION)
        );
        if ($fromPath !== null) {
            $this->extension = $fromPath;

            return $this->extension;
        }

        $origin = $this->getImage()->origin();
        $fromOrigin = $this->normalizedExtension((string) $origin->fileExtension());
        if ($fromOrigin !== null) {
            $this->extension = $fromOrigin;

            return $this->extension;
        }

        $fromMime = ImageMimeEnum::getExtensionByMime($origin->mediaType());
        if ($fromMime !== null) {
            $this->extension = $fromMime;

            return $this->extension;
        }

        throw new \RuntimeException('Unable to determine extension of the image.');
    }

    private function pathWithoutQuery(string $path): string
    {
        if (!str_contains($path, '://') && !str_starts_with($path, '//')) {
            return $path;
        }

        $urlPath = parse_url($path, PHP_URL_PATH);

        return is_string($urlPath) && $urlPath !== '' ? $urlPath : $path;
    }

    private function normalizedExtension(string $extension): ?string
    {
        $ext = strtolower(trim($extension));

        if ($ext === '' || !in_array($ext, ImageMimeEnum::toArray(), true)) {
            return null;
        }

        return $ext;
    }
}
