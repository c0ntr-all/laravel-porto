<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Strategies;

use App\Containers\GallerySection\Media\Contracts\ImageSourceContract;
use App\Containers\GallerySection\Media\Enums\MediaImageMimeEnum;
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
        private readonly string $path
    )
    {
        $info = pathinfo($this->path);

        if (array_key_exists('extension', $info)) {
            $this->extension = strtolower($info['extension']);
        }
        if (array_key_exists('filename', $info)) {
            $this->filename = strtolower($info['filename']);
        }
        if (array_key_exists('basename', $info)) {
            $this->basename = strtolower($info['basename']);
        }
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
            $this->prepareImage();
        }

        return $this->image;
    }

    /**
     * @throws RandomException
     */
    public function getBasename(): string
    {
        if (!$this->basename) {
            $this->prepareBasename();
        }

        return $this->basename;
    }

    /**
     * @throws RandomException
     */
    public function getFilename(): string
    {
        if (!$this->filename) {
            $this->prepareFilename();
        }

        return $this->filename;
    }

    public function getExtension(): string
    {
        if (!$this->extension) {
            $this->prepareExtension();
        }

        return $this->extension;
    }

    private function prepareImage(): void
    {
        if (!$this->image) {
            $fullPath = $this->getFullPath();

            try {
                $this->image = ImageFacade::read($fullPath);
            } catch (NotReadableException $e) {
                throw new \RuntimeException("Unable to load image from path: {$fullPath}");
            }
        }
    }

    /**
     * @throws RandomException
     */
    private function prepareBasename(): void
    {
        if (!$this->basename) {
            $filename = $this->getFilename();
            $extension = $this->getExtension();

            $this->basename = "{$filename}.{$extension}";
        }
    }

    /**
     * @throws RandomException
     */
    private function prepareFilename(): void
    {
        if (!$this->filename) {
            $this->filename = StringHelper::generateFilename();
        }
    }

    private function prepareExtension(): void
    {
        if (!$this->extension) {
            $mime = $this->image->exif()->get('FILE.MimeType');
            if (!$mime) {
                throw new \RuntimeException('Unable to determine MIME type of the image.');
            }

            $this->extension = MediaImageMimeEnum::getExtensionByMime($mime);
        }
    }
}
