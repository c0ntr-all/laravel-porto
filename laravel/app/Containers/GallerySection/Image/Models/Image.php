<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Models;

use App\Containers\AppSection\Attachment\Models\Traits\HasFileableAttachments;
use App\Containers\AppSection\Comment\Models\Traits\HasComments;
use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Enums\ImageMimeEnum;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Models\ActivityLoggableModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Containers\GallerySection\Image\Models
 *
 * @property string $id
 * @property int $user_id
 * @property int $album_id
 * @property string $source
 * @property string $extension
 * @property string|null $external_url
 * @property integer $width
 * @property integer $height
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $base_path
 * @property-read string $list_thumb_path
 * @property-read string $preview_thumb_path
 * @method static Builder|Image newModelQuery()
 * @method static Builder|Image newQuery()
 * @method static Builder|Image query()
 */
class Image extends ActivityLoggableModel
{
    use HasUuids,
        HasUser,
        HasComments,
        HasFileableAttachments;

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::GALLERY_IMAGE;

    protected $table = 'gallery_images';
    protected $fillable = [
        'id',
        'user_id',
        'album_id',
        'source',
        'extension',
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

    public function getPreviewThumbPathAttribute(): string
    {
        return $this->publicStorageUrl($this->relativePath('preview_thumb'));
    }

    public function getBasePathAttribute(): string
    {
        return match ($this->source) {
            FileSourceEnum::WEB->value => (string) $this->external_url,
            FileSourceEnum::WINDOWS->value => url('') . '/api/v1/gallery/images/' . $this->id . '/file',
            default => $this->publicStorageUrl($this->relativePath('base')),
        };
    }

    public function relativePath(string $maskKey): string
    {
        $search = ['{user_id}', '{album_id}', '{file_id}', '{ext}'];
        $replace = [
            (string) $this->user_id,
            (string) $this->album_id,
            (string) $this->id,
            ImageMimeEnum::canonicalize((string) $this->extension)
                ?? strtolower((string) $this->extension),
        ];

        return str_replace($search, $replace, (string) config("image.default.mask.{$maskKey}"));
    }

    private function publicStorageUrl(string $relativePath): string
    {
        return url('') . '/storage/' . ltrim($relativePath, '/');
    }
}
