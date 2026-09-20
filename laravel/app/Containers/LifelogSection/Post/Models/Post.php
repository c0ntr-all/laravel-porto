<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Models;

use App\Containers\AppSection\Attachment\Models\Traits\HasAttachments;
use App\Containers\AppSection\CustomField\Models\Traits\HasCustomFields;
use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\Tag\Models\Traits\HasTags;
use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\AppSection\User\Models\User;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Models\Traits\HasSubjects;
use App\Containers\LifelogSection\Preset\Models\Preset;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use App\Ship\Models\Traits\HasImage;
use App\Ship\Models\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @property PostContentTypeEnum $content_type
 * @property Carbon $date
 * @property Carbon|null $time
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $datetime
 * @property User $user
 * @property Tag[] $tags
 * @property-read EloquentCollection<int, Movie> $movies
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
        HasAttachments,
        HasCustomFields,
        HasSubjects,
        HasUuidV7;

    protected $table = 'lifelog_posts';

    protected $attributes = [
        'content_type' => PostContentTypeEnum::DEFAULT->value,
    ];

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'content_type',
        'date',
        'time',
    ];
    protected $casts = [
        'id' => 'string',
        'content_type' => PostContentTypeEnum::class,
        'date' => 'datetime',
        'time' => 'datetime',
    ];
    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::LL_POST;

    public function presets(): Post|Builder
    {
        return Preset::query()
            ->where('user_id', $this->user_id)
            ->where(function ($query) {
                $query->where('start_date', '<=', $this->datetime)
                    ->where(function ($subQuery) {
                        $subQuery->where('end_date', '>=', $this->datetime)
                            ->orWhereNull('end_date');
                    });
            })
            ->where(function ($query) {
                $this->constrainPresetsByContentType($query);
            });
    }

    public function allRelatedPresets(): Post|Builder
    {
        return Preset::query()
            ->where('user_id', $this->user_id)
            ->where(function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('start_date', '<=', $this->datetime)
                        ->where(function ($dateQuery) {
                            $dateQuery->where('end_date', '>=', $this->datetime)
                                ->orWhereNull('end_date');
                        });
                });
            })
            ->where(function ($query) {
                $this->constrainPresetsByContentType($query);
            });
    }

    private function constrainPresetsByContentType($query): void
    {
        $contentType = $this->content_type->value;

        $query->whereNull('rules')
            ->orWhereNull('rules->content_type')
            ->orWhereJsonContains('rules->content_type', $contentType)
            // legacy: content_type stored as a plain string
            ->orWhere('rules->content_type', $contentType);
    }

    public function getDatetimeAttribute(): Carbon
    {
        $date = $this->date->format('Y-m-d');

        if ($this->time) {
            $time = $this->time->format('H:i:s');

            return Carbon::parse($date . ' ' . $time);
        }

        return Carbon::parse($date . ' 00:00:00');
    }
}
