<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum VideoMimeEnum: string
{
    use Arrayable;

    case MP4 = 'mp4';
    case AVI = 'avi';
    case MOV = 'mov';
    case MKV = 'mkv';
    case ThreeGP = '3gp';

    public function getMime(): string
    {
        return match ($this) {
            self::MP4 => 'video/mp4',
            self::AVI => 'video/avi',
            self::MOV => 'video/mov',
            self::MKV => 'video/mkv',
            self::ThreeGP => 'video/3gp',
        };
    }

    public static function getExtensionByMime(string $mime): string
    {
        return match ($mime) {
            'video/mp4', 'default' => self::MP4->value,
            'video/avi' => self::AVI->value,
            'video/mov' => self::MOV->value,
            'video/mkv' => self::MKV->value,
            'video/3gp' => self::ThreeGP->value
        };
    }
}
