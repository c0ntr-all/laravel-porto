<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Models;

use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends ActivityLoggableModel
{
    use HasUuids;

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
