<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Events;

use App\Containers\MusicSection\Upload\Models\MusicUpload;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UploadProgressed implements ShouldBroadcast
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly MusicUpload $upload,
        public readonly string $stage,
        public readonly int $processed,
        public readonly int $total,
        public readonly string $message = '',
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('music.uploads.' . $this->upload->id)];
    }

    public function broadcastAs(): string
    {
        return 'upload.progressed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->upload->id,
            'status' => $this->upload->status->value,
            'stage' => $this->stage,
            'processed' => $this->processed,
            'total' => $this->total,
            'message' => $this->message,
        ];
    }
}
