<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Events;

use App\Containers\MusicSection\Upload\Models\MusicUpload;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UploadFinished implements ShouldBroadcast
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly MusicUpload $upload
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('music.uploads.' . $this->upload->id)];
    }

    public function broadcastAs(): string
    {
        return 'upload.finished';
    }

    public function broadcastWith(): array
    {
        $artists = $this->upload->relationLoaded('artists')
            ? $this->upload->artists
            : $this->upload->artists()->get();

        return [
            'id' => $this->upload->id,
            'status' => $this->upload->status->value,
            'artist_ids' => $artists->pluck('id')->all(),
            'artist_name' => $artists->pluck('name')->filter()->implode(' / ') ?: null,
            'tracks_found' => $this->upload->tracks_found,
            'tracks_created' => $this->upload->tracks_created,
            'tracks_updated' => $this->upload->tracks_updated,
            'tracks_skipped' => $this->upload->tracks_skipped,
            'tracks_failed' => $this->upload->tracks_failed,
            'error_message' => $this->upload->error_message,
            'stage' => 'finished',
        ];
    }
}
