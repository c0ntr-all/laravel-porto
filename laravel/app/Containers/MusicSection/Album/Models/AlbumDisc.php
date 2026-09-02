<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Models;

use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlbumDisc extends Model
{
    protected $table = 'music_album_discs';

    protected $fillable = [
        'album_id',
        'number',
        'name',
    ];

    protected $casts = [
        'number' => 'integer',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class, 'album_id');
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class, 'disc_id')
                    ->orderBy('number')
                    ->orderBy('name');
    }
}
