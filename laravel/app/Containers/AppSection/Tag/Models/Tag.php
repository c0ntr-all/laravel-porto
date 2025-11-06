<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Models;

use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Models\DBLoggableModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * App\Models\Music\Tag
 *
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Tag> $tags
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereCommon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Tag extends DBLoggableModel
{
    protected $table ='tags';

    protected $guarded = [];
    protected $casts = [
        'id' => 'string'
    ];
    protected ContainerAliasEnum $loggableType = ContainerAliasEnum::TAG;
}
