<?php declare(strict_types=1);

namespace App\Ship\Packages\VideoMetadata\Strategies;

use FFMpeg\FFProbe\DataMapping\Stream;

class MkvStrategy extends BaseVideoStrategy
{
    public function getDuration(Stream $stream): ?string
    {
        $tags = $stream->get('tags');

        if (isset($tags)) {
            return explode('.', $tags['DURATION'])[0];
        }

        return null;
    }
}
