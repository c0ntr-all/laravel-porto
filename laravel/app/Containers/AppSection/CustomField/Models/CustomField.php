<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Models;

use App\Containers\AppSection\CustomField\Enums\CustomFieldTypeEnum;
use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use App\Ship\Models\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomField extends ActivityLoggableModel
{
    use HasUuidV7;
    use HasUser;

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::CUSTOM_FIELD;

    protected $table = 'custom_fields';

    protected $fillable = [
        'user_id',
        'fieldable_type',
        'fieldable_id',
        'type',
        'payload',
        'position',
    ];

    protected $casts = [
        'type' => CustomFieldTypeEnum::class,
        'payload' => 'array',
        'position' => 'integer',
    ];

    public function fieldable(): MorphTo
    {
        return $this->morphTo();
    }
}
