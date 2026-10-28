<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\Models;

use App\Containers\GameSection\Achievement\Models\Achievement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $img
 * @property Carbon|null $air_date
 * @property string|null $developer
 * @property string|null $publisher
 * @property int|null $steam_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Achievement> $achievements
 */
class Game extends Model
{
    use HasFactory;

    protected $table = 'games';

    protected $fillable = [
        'name',
        'description',
        'img',
        'air_date',
        'developer',
        'publisher',
        'steam_id',
    ];

    protected function casts(): array
    {
        return [
            'air_date' => 'date',
            'steam_id' => 'integer',
        ];
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class, 'game_id')->orderBy('id');
    }
}
