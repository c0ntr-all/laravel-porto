<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Models;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MusicUserTrackTag extends Model
{
    protected $table = 'music_user_track_tag';

    protected $fillable = [
        'user_tag_id',
        'track_id',
        'user_id',
    ];

    public function userTag(): BelongsTo
    {
        return $this->belongsTo(MusicUserTag::class, 'user_tag_id');
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
