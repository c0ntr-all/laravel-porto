<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Tasks;

use App\Containers\GallerySection\Media\Enums\MediaImageMimeEnum;
use App\Containers\GallerySection\Media\Enums\MediaVideoMimeEnum;
use App\Ship\Parents\Tasks\Task;

class DefineMediaTypeTask extends Task
{
    public function run(string $name): ?string
    {
        $basename = pathinfo($name);

        if ($basename['extension']) {
            return match($basename['extension']) {
                implode(',', MediaImageMimeEnum::toArray()) => 'image',
                implode(',', MediaVideoMimeEnum::toArray()) => 'video',
                default => null
            };
        }

        return null;
    }
}
