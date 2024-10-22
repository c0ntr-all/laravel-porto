<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Models;

use App\Containers\GallerySection\Album\Models\Album;
use App\Ship\Models\Traits\HasImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Containers\GallerySection\Media\Models
 *
 * @property int $id
 * @property int $user_id
 * @property int $album_id
 * @property string $type
 * @property string $path
 * @property string $description
 * @property boolean $is_web
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $full_image
 * @method static Builder|Media newModelQuery()
 * @method static Builder|Media newQuery()
 * @method static Builder|Media onlyTrashed()
 * @method static Builder|Media query()
 * @method static Builder|Media whereArtistId($value)
 * @method static Builder|Media whereAttributes($value)
 * @method static Builder|Media whereContent($value)
 * @method static Builder|Media whereCreatedAt($value)
 * @method static Builder|Media whereDeletedAt($value)
 * @method static Builder|Media whereEdition($value)
 * @method static Builder|Media whereId($value)
 * @method static Builder|Media whereImage($value)
 * @method static Builder|Media whereName($value)
 * @method static Builder|Media wherePath($value)
 * @method static Builder|Media whereUpdatedAt($value)
 * @method static Builder|Media whereYear($value)
 * @method static Builder|Media withTrashed()
 * @method static Builder|Media withoutTrashed()
 */
class Media extends Model
{
    use HasImage;

    protected $table = 'gallery_media';

    protected $guarded = [];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }
}
