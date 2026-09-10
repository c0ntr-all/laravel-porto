<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Models;

use App\Containers\AppSection\CustomField\Enums\CustomFieldTypeEnum;
use App\Containers\AppSection\User\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomField extends Model
{
    use HasUuids;
    use HasUser;

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
        'fieldable_id' => 'string',
    ];

    public function fieldable(): MorphTo
    {
        return $this->morphTo();
    }
}
