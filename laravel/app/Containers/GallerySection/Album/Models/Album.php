<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\GallerySection\Media\Models\Media;
use App\Ship\Models\Traits\HasImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Containers\GallerySection\Album\Models
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $description
 * @property string|null $image
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $full_image
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
class Album extends Model
{
    use HasImage,
        HasUser;

    protected $table = 'gallery_albums';

    protected $guarded = [];

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }
}
