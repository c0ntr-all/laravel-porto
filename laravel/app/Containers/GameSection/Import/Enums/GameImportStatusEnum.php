<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Enums;

enum GameImportStatusEnum: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
}
