<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Models;

use App\Containers\MusicSection\Track\Models\Track;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MusicTrackTag extends Pivot
{
    protected $table = 'music_track_tag';

    public $incrementing = false;

    public $timestamps = true;

    protected $fillable = [
        'track_id',
        'tag_id',
    ];

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(MusicTag::class, 'tag_id');
    }
}
