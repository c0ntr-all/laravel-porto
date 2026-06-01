<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Models;

use App\Containers\AppSection\Attachment\Models\Traits\HasAttachments;
use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\Tag\Models\Traits\HasTags;
use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Period\Models\Period;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use App\Ship\Models\Traits\HasImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Containers\LifelogSection\Post\Models
 *
 * @property string $id
 * @property int $user_id
 * @property string $title
 * @property string|null $content
 * @property Carbon $date
 * @property Carbon|null $time
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $datetime
 * @property User $user
 * @property Tag[] $tags
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
class Post extends ActivityLoggableModel
{
    use SoftDeletes,
        HasFactory,
        HasImage,
        HasUser,
        HasTags,
        HasAttachments;

    protected $table = 'lifelog_posts';

    protected $guarded = [];
    protected $casts = [
        'id' => 'string',
        'date' => 'datetime',
        'time' => 'datetime',
    ];
    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::LL_POST;

    public function startPeriods(): HasMany
    {
        return $this->hasMany(Period::class, 'start_post_id');
    }

    public function endPeriods(): HasMany
    {
        return $this->hasMany(Period::class, 'end_post_id');
    }

    public function periods(): Post|Builder
    {
        return Period::query()
            ->where('user_id', $this->user_id)
            ->where(function ($query) {
                $query->where(function ($q) {
                    // Пост находится между start и end по ID
                    $q->where('start_post_id', '<', $this->id)
                        ->where(function ($sq) {
                            $sq->where('end_post_id', '>', $this->id)
                                ->orWhereNull('end_post_id');
                        });
                });
            });
    }

    // Все периоды, связанные с постом (для eager loading)
    public function allRelatedPeriods(): Post|Builder
    {
        return Period::query()
            ->where('user_id', $this->user_id)
            ->where(function ($query) {
                $query->where('start_post_id', $this->id)
                    ->orWhere('end_post_id', $this->id)
                    ->orWhere(function ($q) {
                        $q->where('start_post_id', '<', $this->id)
                            ->where(function ($sq) {
                                $sq->where('end_post_id', '>', $this->id)
                                    ->orWhereNull('end_post_id');
                            });
                    });
            });
    }

    public function getDatetimeAttribute(): Carbon
    {
        $datetime = $this->date;
        if (!is_null($this->time)) {
            $datetime = $datetime . ' ' . $this->time;
        }

        return $datetime;
    }
}
