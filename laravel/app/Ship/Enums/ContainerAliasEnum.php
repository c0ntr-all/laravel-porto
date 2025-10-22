<?php declare(strict_types=1);

namespace App\Ship\Enums;

enum ContainerAliasEnum: string
{
    //AppSection
    case USER = 'users';
    case TAG = 'tags';
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
}
