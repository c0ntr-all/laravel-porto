<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum ImageMimeEnum: string
{
    use Arrayable;

    case JPG = 'jpg';
    case JFIF = 'jfif';
    case JPEG = 'jpeg';
    case WEBP = 'webp';
    case PNG = 'png';
    case GIF = 'gif';
    case BMP = 'bmp';

    public function getMime(): string
    {
        return match ($this) {
            self::JPG, self::JPEG, self::JFIF => 'image/jpeg',
            self::WEBP => 'image/webp',
            self::PNG => 'image/png',
            self::GIF => 'image/gif',
            self::BMP => 'image/bmp'
        };
    }

    public static function getExtensionByMime(string $mime): string
    {
        return match ($mime) {
            'image/jpeg', 'default' => self::JPEG->value,
            'image/webp' => self::WEBP->value,
            'image/png' => self::PNG->value,
            'image/gif' => self::GIF->value,
            'image/bmp' => self::BMP->value
        };
    }
}
