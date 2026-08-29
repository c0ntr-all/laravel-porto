<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Models;

use App\Containers\MusicSection\Album\Models\Album;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MusicAlbumAggregatedTag extends Model
{
    protected $table = 'music_album_aggregated_tags';

    protected $fillable = [
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

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(MusicTag::class, 'tag_id');
    }
}
