<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Strategies;

use App\Ship\Helpers\PublicHttps;
use Intervention\Gif\Exceptions\NotReadableException;
use InvalidArgumentException;
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
                $contents = PublicHttps::get($fullPath, PublicHttps::MAX_IMAGE_BYTES);

                $this->image = ImageFacade::read($contents)->orient();
            } catch (InvalidArgumentException $e) {
                throw $e;
            } catch (NotReadableException|\Throwable $e) {
                throw new \RuntimeException(
                    "Unable to load image from path: {$fullPath}. " . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        return $this->image;
    }
}
