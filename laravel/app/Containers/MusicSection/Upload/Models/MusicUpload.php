<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Models;

use App\Containers\AppSection\User\Models\User;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Upload\Enums\UploadStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MusicUpload extends Model
{
    protected $table = 'music_uploads';

    protected $fillable = [
        'user_id',
        'artist_id',
        'artist_name',
        'source_path',
        'status',
        'started_at',
        'finished_at',
        'duration_ms',
        'tracks_found',
        'tracks_created',
        'tracks_updated',
        'tracks_skipped',
        'tracks_failed',
        'albums_created',
        'albums_updated',
        'artists_created',
        'error_message',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => UploadStatusEnum::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'meta' => 'array',
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

    public function tracks(): HasMany
    {
        return $this->hasMany(MusicUploadTrack::class, 'upload_id');
    }
}
