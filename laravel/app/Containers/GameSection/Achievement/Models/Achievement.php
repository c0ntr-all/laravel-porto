<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\Models;

use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\PlayerAchievement\Models\PlayerAchievement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $game_id
 * @property string $name
 * @property string|null $description
 * @property string $steam_api_name
 * @property string|null $icon
 * @property string|null $icon_gray
 * @property bool $hidden
 * @property string|null $player_percent_unlocked
 * @property int|null $steam_internal_key
 * @property int|null $min_progress
 * @property int|null $max_progress
 * @property int|null $group_id
 * @property bool $archived
 * @property int|null $progress_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Game $game
 * @property-read Collection<int, PlayerAchievement> $playerAchievements
 */
class Achievement extends Model
{
    use HasFactory;

    protected $table = 'game_achievements';

    protected $fillable = [
        'game_id',
        'name',
        'description',
        'steam_api_name',
        'icon',
        'icon_gray',
        'hidden',
        'player_percent_unlocked',
        'steam_internal_key',
        'min_progress',
        'max_progress',
        'group_id',
        'archived',
        'progress_type',
    ];

    protected function casts(): array
    {
        return [
            'game_id' => 'integer',
            'hidden' => 'boolean',
            'steam_internal_key' => 'integer',
            'min_progress' => 'integer',
            'max_progress' => 'integer',
            'group_id' => 'integer',
            'archived' => 'boolean',
            'progress_type' => 'integer',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function playerAchievements(): HasMany
    {
        return $this->hasMany(PlayerAchievement::class, 'achievement_id');
    }
}
