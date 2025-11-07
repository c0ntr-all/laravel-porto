<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Models;

use App\Containers\AppSection\Attachment\Models\Traits\HasAttachments;
use App\Containers\AppSection\Tag\Models\Tag;
use App\Containers\AppSection\Tag\Models\Traits\HasTags;
use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use App\Ship\Models\Traits\HasImage;
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
 * @property string $content
 * @property Carbon|null $datetime
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
        'datetime' => 'datetime',
    ];
    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::LL_POST;
}
