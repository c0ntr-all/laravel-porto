<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Models;

use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\DBLoggableMorphPivot;

class Taggable extends DBLoggableMorphPivot
{
    protected $table = 'taggables';
    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::TAG;
}
