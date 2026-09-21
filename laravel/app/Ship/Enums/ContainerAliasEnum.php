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
    case APP_DOCUMENT = 'app_documents';
    case COMMENTS = 'comments';
    case CUSTOM_FIELD = 'custom_fields';
    case COUNTRY = 'countries';
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
    //DashboardSection
    case DASHBOARD = 'dashboards';
    case DASHBOARD_WIDGET = 'dashboard_widgets';
    //GallerySection
    case GALLERY_ALBUM = 'gallery_albums';
    case GALLERY_IMAGE = 'gallery_images';
    case GALLERY_VIDEO = 'gallery_videos';
    //MovieSection
    case MOVIE = 'movies';
    case MOVIE_GENRE = 'movie_genres';
    case MOVIE_IMPORT = 'movie_imports';
    case MOVIE_PERSON = 'movie_persons';
    case MOVIE_PROFESSION = 'movie_professions';
    case MOVIE_CREDIT = 'movie_credits';
    case MOVIE_FOLDER = 'movie_folders';

    public function getContainerMessage(): string
    {
        return match($this) {
            self::USER => 'Пользователь',
            self::TAG => 'Тег',
            self::ATTACHMENT => 'Вложение',
            self::APP_DOCUMENT => 'Документ',
            self::COMMENTS => 'Комментарий',
            self::CUSTOM_FIELD => 'Дополнительное поле',
            self::COUNTRY => 'Страна',
            self::MUSIC_ARTIST => 'Исполнитель',
            self::MUSIC_ALBUM => 'Альбом',
            self::MUSIC_TRACK => 'Трек',
            self::MUSIC_PLAYLIST => 'Плейлист',
            self::MUSIC_TAG => 'Музыкальный тег',
            self::MUSIC_HISTORY => 'История прослушивания',
            self::TM_TASK => 'Задача',
            self::LL_POST => 'Пост',
            self::LL_PRESET => 'Пресет',
            self::DASHBOARD => 'Дашборд',
            self::DASHBOARD_WIDGET => 'Виджет дашборда',
            self::GALLERY_ALBUM => 'Альбом',
            self::GALLERY_IMAGE => 'Изображение',
            self::GALLERY_VIDEO => 'Видео',
            self::MOVIE => 'Фильм',
            self::MOVIE_GENRE => 'Жанр фильма',
            self::MOVIE_IMPORT => 'Импорт фильма',
            self::MOVIE_PERSON => 'Персона',
            self::MOVIE_PROFESSION => 'Профессия',
            self::MOVIE_CREDIT => 'Участие в фильме',
            self::MOVIE_FOLDER => 'Папка фильмов',
        };
    }

    public static function getAliasByModel(Model $model): string
    {
        $morphMap = Relation::morphMap();

        return array_search(get_class($model), $morphMap);
    }

    /**
     * @return list<string>
     */
    public static function attachmentFileableTypes(): array
    {
        return [
            self::GALLERY_IMAGE->value,
            self::GALLERY_VIDEO->value,
            self::APP_DOCUMENT->value,
            'images',
            'videos',
        ];
    }

    /**
     * @deprecated Use attachmentFileableTypes()
     * @return list<string>
     */
    public static function galleryFileableTypes(): array
    {
        return self::attachmentFileableTypes();
    }

    /**
     * @return list<string>
     */
    public static function attachmentAttachableTypes(): array
    {
        return [
            self::LL_POST->value,
            self::TM_TASK->value,
        ];
    }

    /**
     * @return list<string>
     */
    public static function customFieldableTypes(): array
    {
        return [
            self::LL_POST->value,
            self::TM_TASK->value,
        ];
    }

    public static function toCanonicalMorphAlias(string $type): string
    {
        return match ($type) {
            'images', self::GALLERY_IMAGE->value => self::GALLERY_IMAGE->value,
            'videos', self::GALLERY_VIDEO->value => self::GALLERY_VIDEO->value,
            'albums', self::GALLERY_ALBUM->value => self::GALLERY_ALBUM->value,
            default => $type,
        };
    }
}
