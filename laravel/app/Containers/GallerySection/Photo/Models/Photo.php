<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Photo\Models;

use App\Ship\Models\Traits\HasImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Containers\GallerySection\Photo\Models
 *
 * @property int $id
 * @property int $parent_id
 * @property string $name
 * @property string $description
 * @property string|null $image
 * @property string $path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $full_image
 * @method static Builder|Photo newModelQuery()
 * @method static Builder|Photo newQuery()
 * @method static Builder|Photo onlyTrashed()
 * @method static Builder|Photo query()
 * @method static Builder|Photo whereArtistId($value)
 * @method static Builder|Photo whereAttributes($value)
 * @method static Builder|Photo whereContent($value)
 * @method static Builder|Photo whereCreatedAt($value)
 * @method static Builder|Photo whereDeletedAt($value)
 * @method static Builder|Photo whereEdition($value)
 * @method static Builder|Photo whereId($value)
 * @method static Builder|Photo whereImage($value)
 * @method static Builder|Photo whereName($value)
 * @method static Builder|Photo wherePath($value)
 * @method static Builder|Photo whereUpdatedAt($value)
 * @method static Builder|Photo whereYear($value)
 * @method static Builder|Photo withTrashed()
 * @method static Builder|Photo withoutTrashed()
 */
class Photo extends Model
{
    use HasImage;

    protected $table = 'gallery_photos';

    protected $guarded = [];
}
