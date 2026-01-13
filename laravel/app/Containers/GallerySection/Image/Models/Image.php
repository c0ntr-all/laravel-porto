<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Enums\ImageSourceEnum;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
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
 * @property string $extension
 * @property string $external_url
 * @property integer $width
 * @property integer $height
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $base_path
 * @property-read string $list_thumb_path
 * @property-read string $preview_thumb_path
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
class Image extends ActivityLoggableModel
{
    use HasUuids,
        HasUser;

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::GALLERY_IMAGE;

    protected $table = 'gallery_images';
    protected $guarded = [];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * List Thumbnail Path
     *
     * @return string
     */
    public function getListThumbPathAttribute(): string
    {
        $search = ['{user_id}', '{album_id}', '{file_id}', '{ext}'];
        $replace = [$this->user_id, $this->album_id, $this->id, $this->extension];

        return url('') .
            '/storage/' .
            str_replace($search, $replace, config('image.default.mask.list_thumb'));
    }

    /**
     * List Thumbnail Path
     *
     * @return string
     */
    public function getPreviewThumbPathAttribute(): string
    {
        $search = ['{user_id}', '{album_id}', '{file_id}', '{ext}'];
        $replace = [$this->user_id, $this->album_id, $this->id, $this->extension];

        return url('') .
            '/storage/' .
            str_replace($search, $replace, config('image.default.mask.preview_thumb'));
    }

    /**
     * Base path
     *
     * @return string
     */
    public function getBasePathAttribute(): string
    {
        $search = ['{user_id}', '{album_id}', '{file_id}', '{ext}'];
        $replace = [$this->user_id, $this->album_id, $this->id, $this->extension];

        return match($this->source) {
            ImageSourceEnum::DEVICE->value => url('') .
                '/storage/' .
                str_replace($search, $replace, config('image.default.mask.base')),
            ImageSourceEnum::WEB->value => $this->external_url
        };
    }
}
