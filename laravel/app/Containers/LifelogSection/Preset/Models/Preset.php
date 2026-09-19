<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Models;

use App\Containers\AppSection\Tag\Models\Traits\HasTags;
use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Preset\Data\ValueObjects\PresetRules;
use App\Ship\Casts\ValueObjectCast;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use App\Ship\Models\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property string|null $color
 * @property string|null $icon
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property PresetRules|null $rules
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property User $user
 */
class Preset extends ActivityLoggableModel
{
    use SoftDeletes,
        HasFactory,
        HasUser,
        HasTags,
        HasUuidV7;

    protected $table = 'lifelog_presets';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'color',
        'icon',
        'start_date',
        'end_date',
        'rules',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'rules' => ValueObjectCast::class . ':' . PresetRules::class,
    ];

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::LL_PRESET;

    public function isActive(): bool
    {
        return is_null($this->end_date) || $this->end_date->isFuture();
    }

    public function getDurationInDaysAttribute(): ?float
    {
        if (!$this->start_date) {
            return null;
        }

        $end = $this->end_date ?? now();

        return $this->start_date->diffInDays($end);
    }
}
