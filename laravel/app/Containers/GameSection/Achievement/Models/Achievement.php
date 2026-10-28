<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\Models;

use App\Containers\GameSection\Game\Models\Game;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $game_id
 * @property string $name
 * @property string|null $description
 * @property string $steam_api_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Game $game
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
    ];

    protected function casts(): array
    {
        return [
            'game_id' => 'integer',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }
}
