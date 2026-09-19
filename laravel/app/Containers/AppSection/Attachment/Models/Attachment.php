<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Models;

use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use App\Ship\Models\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends ActivityLoggableModel
{
    use HasUuidV7;

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::ATTACHMENT;

    protected $fillable = [
        'user_id',
        'attachable_type',
        'attachable_id',
        'fileable_type',
        'fileable_id',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }
}
