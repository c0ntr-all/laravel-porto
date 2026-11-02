<?php

declare(strict_types=1);

namespace App\Containers\GameSection\PlayerAchievement\Models;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Achievement\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $achievement_id
 * @property bool $achieved
 * @property Carbon|null $unlock_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Achievement $achievement
 */
class PlayerAchievement extends Model
{
    use HasFactory;

    protected $table = 'game_player_achievements';

    protected $fillable = [
        'user_id',
        'achievement_id',
        'achieved',
        'unlock_time',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'achievement_id' => 'integer',
            'achieved' => 'boolean',
            'unlock_time' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class, 'achievement_id');
    }
}
