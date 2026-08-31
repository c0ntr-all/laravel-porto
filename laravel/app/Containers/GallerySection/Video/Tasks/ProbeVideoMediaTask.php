<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Tasks;

use App\Containers\GallerySection\Video\Contracts\VideoSourceContract;
use App\Ship\Packages\VideoMetadata\VideoMetadataFactory;
use App\Ship\Parents\Tasks\Task as ParentTask;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ProbeVideoMediaTask extends ParentTask
{
    /**
     * @return array{media: mixed, width: int, height: int, duration: string|null}
     */
    public function run(VideoSourceContract $source): array
    {
        $media = FFMpeg::fromDisk($source->getDisk())->open($source->getRelativePath());
        $videoStream = $media->getVideoStream();

        $duration = null;
        try {
            $duration = VideoMetadataFactory::create($source->getExtension())->getDuration($videoStream);
        } catch (\Throwable) {
            $duration = null;
        }

        return [
            'media' => $media,
            'width' => (int) ($videoStream?->get('width') ?? 0),
            'height' => (int) ($videoStream?->get('height') ?? 0),
            'duration' => $duration !== null ? (string) $duration : null,
        ];
    }
}
