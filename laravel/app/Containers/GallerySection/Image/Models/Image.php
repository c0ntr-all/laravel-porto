<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Enums\ImageSourceEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Containers\GallerySection\Image\Models
 *
 * @property int $id
 * @property int $user_id
 * @property int $album_id
 * @property string $source
 * @property string $original_path
 * @property string $list_thumb_path
 * @property string $preview_thumb_path
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $full_path
 * @method static Builder|Image newModelQuery()
 * @method static Builder|Image newQuery()
 * @method static Builder|Image onlyTrashed()
 * @method static Builder|Image query()
 * @method static Builder|Image whereArtistId($value)
 * @method static Builder|Image whereAttributes($value)
 * @method static Builder|Image whereContent($value)
 * @method static Builder|Image whereCreatedAt($value)
 * @method static Builder|Image whereDeletedAt($value)
 * @method static Builder|Image whereEdition($value)
 * @method static Builder|Image whereId($value)
 * @method static Builder|Image whereImage($value)
 * @method static Builder|Image whereName($value)
 * @method static Builder|Image wherePath($value)
 * @method static Builder|Image whereUpdatedAt($value)
 * @method static Builder|Image whereYear($value)
 * @method static Builder|Image withTrashed()
 * @method static Builder|Image withoutTrashed()
 */
class Image extends Model
{
    use HasUser;

    protected $table = 'gallery_images';

    protected $guarded = [];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * Full path from root for original path
     *
     * @return string
     */
    public function getFullPathAttribute(): string
    {
        return match($this->source) {
            ImageSourceEnum::WINDOWS->value => url('') . '/windows/images/' . $this->original_path,
            ImageSourceEnum::DEVICE->value => url('') . '/storage/' . $this->original_path,
            ImageSourceEnum::WEB->value => $this->original_path
        };
    }
}
