<?php declare(strict_types=1);

namespace App\Ship\Models\Interfaces;

use App\Ship\Enums\ContainerAliasEnum;

interface DBLoggable
{
    public function getLoggableType(): string;
}
