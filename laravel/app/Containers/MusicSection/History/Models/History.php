<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\Models;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class History extends Model
{
    use SoftDeletes;

    protected $table = 'music_history';

    protected $fillable = [
        'user_id',
        'track_id',
    ];

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
