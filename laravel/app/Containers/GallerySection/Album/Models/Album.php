<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\GallerySection\Album\Enums\SystemAlbumsEnum;
use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Video\Models\Video;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\ActivityLoggableModel;
use App\Ship\Models\Traits\HasImage;
use App\Ship\Models\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Containers\GallerySection\Album\Models
 *
 * @property int $id
 * @property string $uuid
 * @property int|null $user_id
 * @property string|null $system_code
 * @property string $name
 * @property string|null $description
 * @property string|null $image
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $full_image
 * @method static Builder|Album newModelQuery()
 * @method static Builder|Album newQuery()
 * @method static Builder|Album query()
 */
class Album extends ActivityLoggableModel
{
    use HasImage,
        HasUser,
        HasUuidV7;

    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::GALLERY_ALBUM;

    protected $table = 'gallery_albums';

    protected $fillable = [
        'user_id',
        'system_code',
        'name',
        'description',
        'image',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    public function isSystem(): bool
    {
        return $this->system_code !== null && $this->system_code !== '';
    }

    public function isUploadStagingAlbum(): bool
    {
        return $this->system_code === SystemAlbumsEnum::UPLOAD->value;
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }
}
