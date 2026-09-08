<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Events;

use App\Containers\MusicSection\Upload\Models\MusicUpload;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UploadProgressed implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param array{
     *     tracks_processed?: int,
     *     tracks_total?: int,
     *     albums_processed?: int,
     *     albums_total?: int,
     *     artists_processed?: int,
     *     artists_total?: int
     * } $counts
     */
    public function __construct(
        public readonly MusicUpload $upload,
        public readonly string $stage,
        public readonly int $processed,
        public readonly int $total,
        public readonly string $message = '',
        public readonly array $counts = [],
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
            'tracks_processed' => (int) ($this->counts['tracks_processed'] ?? $this->processed),
            'tracks_total' => (int) ($this->counts['tracks_total'] ?? $this->total),
            'albums_processed' => (int) ($this->counts['albums_processed'] ?? 0),
            'albums_total' => (int) ($this->counts['albums_total'] ?? 0),
            'artists_processed' => (int) ($this->counts['artists_processed'] ?? 0),
            'artists_total' => (int) ($this->counts['artists_total'] ?? 0),
        ];
    }
}
