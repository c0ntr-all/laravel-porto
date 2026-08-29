<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Models;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Album\Models\Album;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MusicUserAlbumAggregatedTag extends Model
{
    protected $table = 'music_user_album_aggregated_tags';

    protected $fillable = [
        'user_id',
        'album_id',
        'tag_id',
        'tracks_count',
        'percentage',
    ];

    protected function casts(): array
    {
        return [
            'tracks_count' => 'integer',
            'percentage' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(MusicUserTag::class, 'tag_id');
    }
}
