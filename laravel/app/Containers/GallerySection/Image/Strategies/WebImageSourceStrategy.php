<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Strategies;

use Illuminate\Support\Facades\Http;
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
                $contents = Http::timeout(30)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0',
                    ])
                    ->withOptions(['verify' => false])
                    ->get($fullPath)
                    ->throw()
                    ->body();

                $this->image = ImageFacade::read($contents);
            } catch (NotReadableException|\Throwable $e) {
                throw new \RuntimeException("Unable to load image from path: {$fullPath}");
            }
        }

        return $this->image;
    }
}
