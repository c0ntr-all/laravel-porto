<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Models;

use App\Containers\MusicSection\Track\Models\Track;
use App\Containers\MusicSection\Upload\Enums\UploadTrackStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MusicUploadTrack extends Model
{
    protected $table = 'music_upload_tracks';

    protected $fillable = [
        'upload_id',
        'track_id',
        'album_name',
        'track_name',
        'source_path',
        'status',
        'snapshot',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'status' => UploadTrackStatusEnum::class,
            'snapshot' => 'array',
        ];
    }

    public function upload(): BelongsTo
    {
        return $this->belongsTo(MusicUpload::class, 'upload_id');
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
