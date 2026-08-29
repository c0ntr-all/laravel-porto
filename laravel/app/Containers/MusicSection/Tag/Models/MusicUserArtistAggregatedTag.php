<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Models;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Artist\Models\Artist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MusicUserArtistAggregatedTag extends Model
{
    protected $table = 'music_user_artist_aggregated_tags';

    protected $fillable = [
        'user_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(MusicUserTag::class, 'tag_id');
    }
}
