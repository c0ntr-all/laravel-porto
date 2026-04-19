<?php declare(strict_types=1);

namespace App\Ship\Packages\VideoMetadata\Strategies;

use App\Ship\Packages\VideoMetadata\Contracts\VideoMetadataStrategyContract;
use FFMpeg\FFProbe\DataMapping\Stream;

abstract class BaseVideoStrategy implements VideoMetadataStrategyContract
{
    public function getDuration(Stream $stream): ?string
    {
        return $stream->get('duration');
    }

    public function getWidth(Stream $stream): ?int
    {
         return $stream->get('width');
    }

    public function getHeight(Stream $stream): ?int
    {
        return $stream->get('height');
    }

    public function getBitrate(Stream $stream): ?string
    {
        return $stream->get('bit_rate');
    }

    public function getFramerate(Stream $stream): ?string
    {
        return $stream->get('avg_frame_rate');
    }

    public function getAspectRatio(Stream $stream): ?string
    {
        return $stream->get('display_aspect_ratio');
    }

    public function getCodecName(Stream $stream): ?string
    {
        return $stream->get('codec_name');
    }
}
