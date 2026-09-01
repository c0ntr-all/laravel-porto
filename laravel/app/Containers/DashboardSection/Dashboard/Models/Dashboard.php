<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\DashboardSection\Widget\Models\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property bool $is_default
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Widget> $widgets
 * @mixin \Eloquent
 */
class Dashboard extends Model
{
    use HasUser,
        SoftDeletes;

    protected $table = 'dashboards';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'bool',
        'sort_order' => 'integer',
    ];

    public function widgets(): HasMany
    {
        return $this->hasMany(Widget::class)->orderBy('sort_order')->orderBy('id');
    }
}
