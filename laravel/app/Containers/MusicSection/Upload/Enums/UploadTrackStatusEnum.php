<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Enums;

enum UploadTrackStatusEnum: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Skipped = 'skipped';
    case Failed = 'failed';
}
