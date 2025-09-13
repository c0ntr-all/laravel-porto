<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\AppSection\User\Models\User;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Models\Traits\HasImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Containers\GallerySection\Album\Models
 *
 * @property string $id
 * @property int $user_id
 * @property string $title
 * @property string $content
 * @property Carbon|null $datetime
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $user
 * @method static Builder|Album newModelQuery()
 * @method static Builder|Album newQuery()
 * @method static Builder|Album onlyTrashed()
 * @method static Builder|Album query()
 * @method static Builder|Album whereArtistId($value)
 * @method static Builder|Album whereAttributes($value)
 * @method static Builder|Album whereContent($value)
 * @method static Builder|Album whereCreatedAt($value)
 * @method static Builder|Album whereDeletedAt($value)
 * @method static Builder|Album whereEdition($value)
 * @method static Builder|Album whereId($value)
 * @method static Builder|Album whereImage($value)
 * @method static Builder|Album whereName($value)
 * @method static Builder|Album wherePath($value)
 * @method static Builder|Album whereUpdatedAt($value)
 * @method static Builder|Album whereYear($value)
 * @method static Builder|Album withTrashed()
 * @method static Builder|Album withoutTrashed()
 */
class Post extends Model
{
    use SoftDeletes,
        HasImage,
        HasUser;

    protected $table = 'lifelog_posts';

    protected $guarded = [];
    protected $casts = [
        'id' => 'string',
        'datetime' => 'datetime',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }
}
