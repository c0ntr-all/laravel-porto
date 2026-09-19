<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Enums;

enum MovieImportStatusEnum: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
}
