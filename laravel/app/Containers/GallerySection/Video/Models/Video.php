<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Models;

use App\Containers\AppSection\Comment\Models\Traits\HasComments;
use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\GallerySection\Album\Models\Album;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Models\ActivityLoggableModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Containers\GallerySection\Video\Models
 *
 * @property string $id
 * @property int $user_id
 * @property int $album_id
 * @property string $source
 * @property string|null $extension
 * @property string|null $duration
 * @property string|null $original_name
 * @property string|null $external_url
 * @property integer $width
 * @property integer $height
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $base_path
 * @property-read string $list_thumb_path
 * @method static Builder|Video newModelQuery()
 * @method static Builder|Video newQuery()
 * @method static Builder|Video query()
 */
class Video extends ActivityLoggableModel
{
    use HasUuids,
        HasUser,
        HasComments;

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::GALLERY_VIDEO;

    protected $table = 'gallery_videos';
    protected $fillable = [
        'id',
        'user_id',
        'album_id',
        'source',
        'extension',
        'duration',
        'original_name',
        'external_url',
        'width',
        'height',
        'description',
        'saved_from_id',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function getListThumbPathAttribute(): string
    {
        return $this->publicStorageUrl($this->relativePath('list_thumb'));
    }

    public function getBasePathAttribute(): string
    {
        return match ($this->source) {
            FileSourceEnum::WEB->value => (string) $this->external_url,
            FileSourceEnum::WINDOWS->value => url('') . '/api/v1/gallery/videos/' . $this->id . '/file',
            default => $this->publicStorageUrl($this->relativePath('base')),
        };
    }

    public function relativePath(string $maskKey): string
    {
        $extension = $maskKey === 'list_thumb' ? 'jpg' : (string) $this->extension;
        $search = ['{user_id}', '{album_id}', '{file_id}', '{ext}'];
        $replace = [(string) $this->user_id, (string) $this->album_id, (string) $this->id, $extension];

        return str_replace($search, $replace, (string) config("video.default.mask.{$maskKey}"));
    }

    private function publicStorageUrl(string $relativePath): string
    {
        return url('') . '/storage/' . ltrim($relativePath, '/');
    }
}
