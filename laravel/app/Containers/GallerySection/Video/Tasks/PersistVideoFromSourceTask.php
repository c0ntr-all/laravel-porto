<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Tasks;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Video\Contracts\VideoSourceContract;
use App\Containers\GallerySection\Video\Data\DTO\CreateVideoDto;
use App\Containers\GallerySection\Video\Models\Video;
use App\Containers\GallerySection\Video\Strategies\WebVideoSourceStrategy;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class PersistVideoFromSourceTask extends ParentTask
{
    public function __construct(
        private readonly ProbeVideoMediaTask $probeVideoMediaTask,
        private readonly CreateVideoListThumbTask $createVideoListThumbTask,
        private readonly CreateVideoTask $createVideoTask,
    ) {
    }

    public function run(
        Album $album,
        int $userId,
        string $source,
        VideoSourceContract $videoSource,
        ?string $fileId = null,
    ): Video {
        $uuid = $fileId ?? Uuid::uuid4()->toString();
        $width = 0;
        $height = 0;
        $duration = null;

        try {
            $probed = $this->probeVideoMediaTask->run($videoSource);
            $width = $probed['width'];
            $height = $probed['height'];
            $duration = $probed['duration'];
            $this->createVideoListThumbTask->run(
                $probed['media'],
                (string) $userId,
                (string) $album->id,
                $uuid,
            );
        } catch (\Throwable $exception) {
            Log::warning("Unable to probe gallery video({$uuid}): " . $exception->getMessage());
        } finally {
            if ($videoSource instanceof WebVideoSourceStrategy) {
                $videoSource->cleanup();
            }
        }

        $createVideoDto = CreateVideoDto::from([
            'id' => $uuid,
            'user_id' => $userId,
            'album_id' => (int) $album->id,
            'source' => $source,
            'duration' => $duration,
            'original_name' => $videoSource->getOriginalName(),
            'extension' => $videoSource->getExtension(),
            'external_url' => $videoSource->getStoredExternalUrl(),
            'width' => $width,
            'height' => $height,
        ]);

        return $this->createVideoTask->run($createVideoDto);
    }
}
