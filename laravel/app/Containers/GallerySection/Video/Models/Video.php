<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\GallerySection\Album\Models\Album;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Containers\GallerySection\Video\Models
 *
 * @property int $id
 * @property int $user_id
 * @property int $album_id
 * @property string $source
 * @property string $extension
 * @property integer $width
 * @property integer $height
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $base_path
 * @property-read string $list_thumb_path
 * @method static Builder|Video newModelQuery()
 * @method static Builder|Video newQuery()
 * @method static Builder|Video onlyTrashed()
 * @method static Builder|Video query()
 * @method static Builder|Video whereArtistId($value)
 * @method static Builder|Video whereAttributes($value)
 * @method static Builder|Video whereContent($value)
 * @method static Builder|Video whereCreatedAt($value)
 * @method static Builder|Video whereDeletedAt($value)
 * @method static Builder|Video whereEdition($value)
 * @method static Builder|Video whereId($value)
 * @method static Builder|Video whereVideo($value)
 * @method static Builder|Video whereName($value)
 * @method static Builder|Video wherePath($value)
 * @method static Builder|Video whereUpdatedAt($value)
 * @method static Builder|Video whereYear($value)
 * @method static Builder|Video withTrashed()
 * @method static Builder|Video withoutTrashed()
 */
class Video extends ActivityLoggableModel
{
    use HasUuids,
        HasUser;

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::GALLERY_VIDEO;

    protected $table = 'gallery_videos';
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
            str_replace($search, $replace, config('video.default.mask.list_thumb'));
    }

    /**
     * Full path from root for original path
     *
     * @return string
     */
    public function getBasePathAttribute(): string
    {
        $search = ['{user_id}', '{album_id}', '{file_id}', '{ext}'];
        $replace = [$this->user_id, $this->album_id, $this->id, $this->extension];

        return url('') .
            '/storage/' .
            str_replace($search, $replace, config('video.default.mask.base'));
    }
}
