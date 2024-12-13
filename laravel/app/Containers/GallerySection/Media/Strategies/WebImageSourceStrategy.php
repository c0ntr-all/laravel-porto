<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Strategies;

use Intervention\Gif\Exceptions\NotReadableException;
use Intervention\Image\Image;
use Intervention\Image\Laravel\Facades\Image as ImageFacade;

class WebImageSourceStrategy extends AbstractImageSourceStrategy
{
    private ?Image $image = null;

    public function getImage(): Image
    {
        if (!$this->image) {
            $fullPath = $this->getFullPath();

            try {
                $this->image = ImageFacade::read(file_get_contents($fullPath));
            } catch (NotReadableException $e) {
                throw new \RuntimeException("Unable to load image from path: {$fullPath}");
            }
        }

        return $this->image;
    }
}
