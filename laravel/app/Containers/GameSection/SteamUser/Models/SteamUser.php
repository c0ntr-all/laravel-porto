<?php

declare(strict_types=1);

namespace App\Containers\GameSection\SteamUser\Models;

use App\Containers\AppSection\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Steam Web API player summary stored as the user's profile.
 * API fields arrive camelCase (`personaname`) and are mapped to these columns on import.
 *
 * @property int $user_id
 * @property string $steam_id
 * @property int $community_visibility_state
 * @property int|null $profile_state
 * @property string $persona_name
 * @property int|null $comment_permission
 * @property string|null $profile_url
 * @property string|null $avatar
 * @property string|null $avatar_medium
 * @property string|null $avatar_full
 * @property string|null $avatar_hash
 * @property Carbon|null $last_logoff
 * @property int $persona_state
 * @property string|null $real_name
 * @property string|null $primary_clan_id
 * @property Carbon|null $time_created
 * @property int|null $persona_state_flags
 * @property string|null $loc_country_code
 * @property string|null $loc_state_code
 * @property int|null $loc_city_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
class SteamUser extends Model
{
    use HasFactory;

    protected $table = 'steam_users';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'steam_id',
        'community_visibility_state',
        'profile_state',
        'persona_name',
        'comment_permission',
        'profile_url',
        'avatar',
        'avatar_medium',
        'avatar_full',
        'avatar_hash',
        'last_logoff',
        'persona_state',
        'real_name',
        'primary_clan_id',
        'time_created',
        'persona_state_flags',
        'loc_country_code',
        'loc_state_code',
        'loc_city_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'community_visibility_state' => 'integer',
            'profile_state' => 'integer',
            'comment_permission' => 'integer',
            'last_logoff' => 'datetime',
            'persona_state' => 'integer',
            'time_created' => 'datetime',
            'persona_state_flags' => 'integer',
            'loc_city_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
