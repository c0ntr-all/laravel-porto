<?php declare(strict_types=1);

namespace App\Ship\Enums;

use App\Ship\Enums\Traits\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

enum ContainerAliasEnum: string
{
    use Arrayable;

    //AppSection
    case USER = 'users';
    case TAG = 'tags';
    case ATTACHMENT = 'attachments';
    case COMMENTS = 'comments';
    //MusicSection
    case MUSIC_ARTIST = 'music_artists';
    case MUSIC_ALBUM = 'music_albums';
    case MUSIC_TRACK = 'music_tracks';
    case MUSIC_PLAYLIST = 'music_playlists';
    case MUSIC_TAG = 'music_tags';
    case MUSIC_HISTORY = 'music_history';
    //TaskManagerSection
    case TM_TASK = 'tm_tasks';
    //LifelogSection
    case LL_POST = 'll_posts';
    case LL_PRESET = 'll_presets';
    //GallerySection
    case GALLERY_ALBUM = 'gallery_albums';
    case GALLERY_IMAGE = 'gallery_images';
    case GALLERY_VIDEO = 'gallery_videos';

    public function getContainerMessage(): string
    {
        return match($this) {
            self::USER => 'Пользователь',
            self::TAG => 'Тег',
            self::ATTACHMENT => 'Вложение',
            self::COMMENTS => 'Комментарий',
            self::MUSIC_ARTIST => 'Исполнитель',
            self::MUSIC_ALBUM => 'Альбом',
            self::MUSIC_TRACK => 'Трек',
            self::MUSIC_PLAYLIST => 'Плейлист',
            self::MUSIC_TAG => 'Музыкальный тег',
            self::MUSIC_HISTORY => 'История прослушивания',
            self::TM_TASK => 'Задача',
            self::LL_POST => 'Пост',
            self::LL_PRESET => 'Пресет',
            self::GALLERY_ALBUM => 'Альбом',
            self::GALLERY_IMAGE => 'Изображение',
            self::GALLERY_VIDEO => 'Видео',
        };
    }

    public static function getAliasByModel(Model $model): string
    {
        $morphMap = Relation::morphMap();

        return array_search(get_class($model), $morphMap);
    }
}
