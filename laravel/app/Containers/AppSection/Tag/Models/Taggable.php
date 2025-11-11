<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Models;

use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableMorphPivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Taggable extends ActivityLoggableMorphPivot
{
    protected $table = 'taggables';
    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::TAG;

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'tag_id', 'id');
    }
}
