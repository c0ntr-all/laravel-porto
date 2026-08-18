<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $icon
 * @property string|null $color
 * @property string|null $description
 * @property int|null $parent_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Tag|null $parent
 * @property-read Collection<int, Tag> $children
 */
class Tag extends ActivityLoggableModel
{
    use HasFactory,
        HasUser;

    protected $table = 'tags';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'icon',
        'color',
        'description',
        'parent_id',
    ];

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::TAG;

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
