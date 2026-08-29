<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Models;

use App\Containers\MusicSection\Artist\Models\Artist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MusicArtistAggregatedTag extends Model
{
    protected $table = 'music_artist_aggregated_tags';

    protected $fillable = [
        'artist_id',
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

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(MusicTag::class, 'tag_id');
    }
}
