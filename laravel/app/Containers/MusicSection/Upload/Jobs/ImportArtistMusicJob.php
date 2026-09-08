<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Jobs;

use App\Containers\MusicSection\Upload\Models\MusicUpload;
use App\Containers\MusicSection\Upload\Tasks\ImportArtistMusicTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportArtistMusicJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 1800;
    public int $tries = 1;

    // Dispatched asynchronously from CreateUploadAction so the UI can follow Echo progress.

    public function __construct(
        public readonly MusicUpload $upload
    ) {
    }

    public function handle(ImportArtistMusicTask $importArtistMusicTask): void
    {
        $importArtistMusicTask->run($this->upload);
    }
}
