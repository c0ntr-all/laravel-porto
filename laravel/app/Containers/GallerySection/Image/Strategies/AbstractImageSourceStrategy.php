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
        private readonly string $path
    )
    {
        if (!$this->extension) {
            $info = pathinfo($this->path);
            $filename = $info['filename'];
            $extension = $info['extension'];

            $this->filename = $filename;

            $mimes = ImageMimeEnum::toArray();

            if (in_array($extension, $mimes)) {
                $this->extension = $extension;
            }
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
        if (!$this->extension) {
            // 1️⃣ Попробуем взять из пути
            $ext = strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
            if ($ext && in_array($ext, ImageMimeEnum::toArray())) {
                $this->extension = $ext;
                return $this->extension;
            }

            // 2️⃣ Попробуем взять через mime от Intervention
            $mime = $this->getImage()->mime();
            $this->extension = ImageMimeEnum::getExtensionByMime($mime);

            if (!$this->extension) {
                throw new \RuntimeException('Unable to determine extension of the image.');
            }
        }

        return $this->extension;
    }
}
