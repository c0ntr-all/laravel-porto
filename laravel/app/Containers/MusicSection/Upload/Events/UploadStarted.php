<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Events;

use App\Containers\MusicSection\Upload\Models\MusicUpload;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UploadStarted implements ShouldBroadcastNow
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
        return 'upload.started';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->upload->id,
            'status' => $this->upload->status->value,
            'source_path' => $this->upload->source_path,
            'stage' => 'started',
        ];
    }
}
