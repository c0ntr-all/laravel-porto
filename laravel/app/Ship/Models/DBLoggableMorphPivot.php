<?php declare(strict_types=1);

namespace App\Ship\Models;

use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\Interfaces\DBLoggable;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

abstract class DBLoggableMorphPivot extends MorphPivot implements DBLoggable
{
    protected ContainerAliasEnum $loggableType;

    public function getLoggableType(): string
    {
        return $this->loggableType->value;
    }
}
