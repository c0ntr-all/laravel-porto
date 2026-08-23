<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Enums;

enum UploadStatusEnum: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case CompletedWithErrors = 'completed_with_errors';
    case Failed = 'failed';
}
