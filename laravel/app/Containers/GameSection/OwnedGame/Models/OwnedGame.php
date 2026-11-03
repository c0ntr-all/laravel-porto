<?php

declare(strict_types=1);

namespace App\Containers\GameSection\OwnedGame\Models;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Game\Models\Game;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $game_id
 * @property int $playtime_forever
 * @property Carbon|null $last_played_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Game $game
 */
class OwnedGame extends Model
{
    use HasFactory;

    protected $table = 'game_owned_games';

    protected $fillable = [
        'user_id',
        'game_id',
        'playtime_forever',
        'last_played_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'game_id' => 'integer',
            'playtime_forever' => 'integer',
            'last_played_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }
}
