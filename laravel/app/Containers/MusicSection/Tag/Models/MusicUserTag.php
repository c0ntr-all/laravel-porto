<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Models;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MusicUserTag extends Model
{
    protected $table = 'music_user_tags';

    protected $fillable = [
        'user_id',
        'name',
        'color',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'music_user_track_tag', 'user_tag_id', 'track_id')
                    ->withPivot('user_id')
                    ->withTimestamps();
    }
}
