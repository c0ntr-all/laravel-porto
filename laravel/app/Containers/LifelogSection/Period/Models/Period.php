<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Containers\LifelogSection\Period\Models
 *
 * @property string $id
 * @property int $user_id
 * @property int $start_post_id
 * @property int $end_post_id
 * @property string $title
 * @property string|null $description
 * @property string|null $color
 * @property string|null $icon
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property User $user
 * @property Post $startPost
 * @property Post $endPost
 * @method static Builder|Post newModelQuery()
 * @method static Builder|Post newQuery()
 * @method static Builder|Post onlyTrashed()
 * @method static Builder|Post query()
 * @method static Builder|Post whereUserId($value)
 * @method static Builder|Post whereContent($value)
 * @method static Builder|Post whereCreatedAt($value)
 * @method static Builder|Post whereId($value)
 * @method static Builder|Post whereTitle($value)
 * @method static Builder|Post whereUpdatedAt($value)
 * @method static Builder|Post withTrashed()
 * @method static Builder|Post withoutTrashed()
 */
class Period extends ActivityLoggableModel
{
    use SoftDeletes,
        HasFactory,
        HasUser;

    protected $table = 'lifelog_periods';

    protected $fillable = [
        'title',
        'description',
        'color',
        'icon',
        'start_post_id',
        'end_post_id',
    ];

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::LL_PERIOD;

    public function startPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'start_post_id');
    }

    public function endPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'end_post_id');
    }

    public function getStartDateAttribute(): ?Carbon
    {
        return $this->startPost?->datetime;
    }

    public function getEndDateAttribute(): ?Carbon
    {
        return $this->endPost?->datetime;
    }

    public function isActive(): bool
    {
        return is_null($this->end_post_id);
    }

    public function getDurationInDaysAttribute(): ?float
    {
        if (!$this->start_date) return null;

        $end = $this->end_date ?? now();

        return $this->start_date->diffInDays($end);
    }
}
