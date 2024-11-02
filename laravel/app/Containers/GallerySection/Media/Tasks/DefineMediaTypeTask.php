<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Tasks;

use App\Containers\GallerySection\Media\Enums\MediaImageMimeEnum;
use App\Containers\GallerySection\Media\Enums\MediaVideoMimeEnum;
use App\Ship\Parents\Tasks\Task;

class DefineMediaTypeTask extends Task
{
    public function run(string $name): ?string
    {
        $extension = pathinfo($name, PATHINFO_EXTENSION);

        if ($extension) {
            $imageMimeTypes = MediaImageMimeEnum::toArray();
            $videoMimeTypes = MediaVideoMimeEnum::toArray();

            return in_array($extension, $imageMimeTypes, true) ? 'image' :
                (in_array($extension, $videoMimeTypes, true) ? 'video' : null);
        }

        return null;
    }
}
