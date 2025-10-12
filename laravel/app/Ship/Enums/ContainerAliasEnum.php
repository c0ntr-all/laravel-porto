<?php declare(strict_types=1);

namespace App\Ship\Enums;

enum ContainerAliasEnum: string
{
    //AppSection
    case USER = 'users';
    case TAG = 'tags';
    //MusicSection
    case ARTIST = 'artists';
    case ALBUM = 'albums';
    case TRACK = 'tracks';
    //TaskManagerSection
    case TASK = 'tasks';
    //LifelogSection
    case POST = 'posts';
}
