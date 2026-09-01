<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Models;

use App\Containers\AppSection\User\Models\Traits\HasUser;
use App\Containers\DashboardSection\Dashboard\Models\Dashboard;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $dashboard_id
 * @property int $user_id
 * @property string $type
 * @property string|null $title
 * @property WidgetSizeEnum $size
 * @property int $sort_order
 * @property array<string, mixed> $config
 * @property bool $is_enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Dashboard $dashboard
 * @mixin \Eloquent
 */
class Widget extends Model
{
    use HasUser,
        SoftDeletes;

    /** @var array<string, mixed>|null */
    public ?array $resolvedPayload = null;

    protected $table = 'dashboard_widgets';

    protected $fillable = [
        'dashboard_id',
        'user_id',
        'type',
        'title',
        'size',
        'sort_order',
        'config',
        'is_enabled',
    ];

    protected $casts = [
        'size' => WidgetSizeEnum::class,
        'sort_order' => 'integer',
        'config' => 'array',
        'is_enabled' => 'bool',
    ];

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }
}
