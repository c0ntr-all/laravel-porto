<?php declare(strict_types=1);

namespace App\Ship\Packages\VideoMetadata;

use App\Ship\Packages\VideoMetadata\Strategies\AviStrategy;
use App\Ship\Packages\VideoMetadata\Strategies\BaseVideoStrategy;
use App\Ship\Packages\VideoMetadata\Strategies\MkvStrategy;
use App\Ship\Packages\VideoMetadata\Strategies\Mp4Strategy;

class VideoMetadataFactory
{
    public static function create(string $extension): BaseVideoStrategy
    {
        return match ($extension) {
            'avi' => new AviStrategy(),
            'mkv' => new MkvStrategy(),
            'mp4', 'm4v', 'mp4v', 'm4a' => new Mp4Strategy(),
        };
    }
}
