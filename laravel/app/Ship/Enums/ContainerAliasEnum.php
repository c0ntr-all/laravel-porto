<?php declare(strict_types=1);

namespace App\Ship\Enums;

use App\Ship\Enums\Traits\Arrayable;

enum ContainerAliasEnum: string
{
    use Arrayable;

    //AppSection
    case USER = 'users';
    case TAG = 'tags';
    case ATTACHMENT = 'attachments';
    //MusicSection
    case MUSIC_ARTIST = 'music_artists';
    case MUSIC_ALBUM = 'music_albums';
    case MUSIC_TRACK = 'music_tracks';
    //TaskManagerSection
    case TM_TASK = 'tm_tasks';
    //LifelogSection
    case LL_POST = 'll_posts';
    //GallerySection
    case GALLERY_IMAGE = 'gallery_images';
    case GALLERY_VIDEO = 'gallery_videos';

    public function getContainerMessage(): string
    {
        return match($this) {
            self::USER => 'Пользователь',
            self::TAG => 'Тег',
            self::MUSIC_ARTIST => 'Исполнитель',
            self::MUSIC_ALBUM => 'Альбом',
            self::MUSIC_TRACK => 'Трек',
            self::TM_TASK => 'Задача',
            self::LL_POST => 'Пост',
            self::GALLERY_IMAGE => 'Изображение',
            self::GALLERY_VIDEO => 'Видео',
        };
    }
}
